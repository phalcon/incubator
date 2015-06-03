<?php namespace Phalcon\Mvc\Model\EagerLoading;

use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * Represents a level in the relations tree to be eagerly loaded
 */
final class EagerLoad
{
    /** @var RelationInterface */
    private $relation;
    /** @var null|callable */
    private $constraints;
    /** @var Loader|EagerLoad */
    private $parent;
    /** @var null|Phalcon\Mvc\ModelInterface[] */
    private $subject;
    /** @var boolean */
    private static $isPhalcon2;

    /**
     * @param RelationInterface
     * @param null|callable $constraints
     * @param Loader|EagerLoad $parent
     */
    public function __construct(Relation $relation, $constraints, $parent)
    {
        if (static::$isPhalcon2 === null) {
            static::$isPhalcon2 = version_compare(\Phalcon\Version::get(), '2.0.0') >= 0;
        }

        $this->relation    = $relation;
        $this->constraints = is_callable($constraints) ? $constraints : null;
        $this->parent      = $parent;
    }

    /**
     * @return null|Phalcon\Mvc\ModelInterface[]
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Executes each db query needed
     *
     * Note: The {$alias} property is set two times because Phalcon Model ignores
     * empty arrays when overloading property set.
     *
     * Also {@see https://github.com/stibiumz/phalcon.eager-loading/issues/1}
     *
     * @return $this
     */
    public function load()
    {
        $relation = $this->relation;

        $alias                = $relation->getOptions();
        $alias                = strtolower($alias['alias']);
        $relField             = $relation->getFields();
        $relReferencedModel   = $relation->getReferencedModel();
        $relReferencedField   = $relation->getReferencedFields();
        $relIrModel           = $relation->getIntermediateModel();
        $relIrField           = $relation->getIntermediateFields();
        $relIrReferencedField = $relation->getIntermediateReferencedFields();

        // PHQL has problems with this slash
        if ($relReferencedModel[0] === '\\') {
            $relReferencedModel = ltrim($relReferencedModel, '\\');
        }

        $bindValues = [];

        foreach ($this->parent->getSubject() as $record) {
            $bindValues[$record->readAttribute($relField)] = true;
        }

        $bindValues = array_keys($bindValues);

        $subjectSize         = count($this->parent->getSubject());
        $isManyToManyForMany = false;

        $builder = new QueryBuilder;
        $builder->from($relReferencedModel);

        if ($isThrough = $relation->isThrough()) {
            if ($subjectSize === 1) {
                // The query is for a single model
                $builder
                    ->innerJoin(
                        $relIrModel,
                        sprintf(
                            '[%s].[%s] = [%s].[%s]',
                            $relIrModel,
                            $relIrReferencedField,
                            $relReferencedModel,
                            $relReferencedField
                        )
                    )
                    ->inWhere("[{$relIrModel}].[{$relIrField}]", $bindValues)
                ;
            } else {
                // The query is for many models, so it's needed to execute an
                // extra query
                $isManyToManyForMany = true;

                $relIrValues = new QueryBuilder;
                $relIrValues = $relIrValues
                    ->from($relIrModel)
                    ->inWhere("[{$relIrModel}].[{$relIrField}]", $bindValues)
                    ->getQuery()
                    ->execute()
                    ->setHydrateMode(Resultset::HYDRATE_ARRAYS)
                ;

                $bindValues = $modelReferencedModelValues = array ();
                
                foreach ($relIrValues as $row) {
                    $bindValues[$row[$relIrReferencedField]] = true;
                    $modelReferencedModelValues[$row[$relIrField]][$row[$relIrReferencedField]] = true;
                }

                unset ($relIrValues, $row);

                $builder->inWhere("[{$relReferencedField}]", array_keys($bindValues));
            }
        } else {
            $builder->inWhere("[{$relReferencedField}]", $bindValues);
        }

        if ($this->constraints) {
            call_user_func($this->constraints, $builder);
        }

        $records = array ();

        if ($isManyToManyForMany) {
            foreach ($builder->getQuery()->execute() as $record) {
                $records[$record->readAttribute($relReferencedField)] = $record;
            }

            foreach ($this->parent->getSubject() as $record) {
                $referencedFieldValue = $record->readAttribute($relField);

                if (isset ($modelReferencedModelValues[$referencedFieldValue])) {
                    $referencedModels = array ();

                    foreach ($modelReferencedModelValues[$referencedFieldValue] as $idx => $_) {
                        $referencedModels[] = $records[$idx];
                    }

                    $record->{$alias} = $referencedModels;

                    if (static::$isPhalcon2) {
                        $record->{$alias} = null;
                        $record->{$alias} = $referencedModels;
                    }
                } else {
                    $record->{$alias} = null;
                    $record->{$alias} = array ();
                }
            }

            $records = array_values($records);
        } else {
            // We expect a single object or a set of it
            $isSingle = ! $isThrough && (
                $relation->getType() === Relation::HAS_ONE ||
                $relation->getType() === Relation::BELONGS_TO
            );

            if ($subjectSize === 1) {
                // Keep all records in memory
                foreach ($builder->getQuery()->execute() as $record) {
                    $records[] = $record;
                }

                $record = $this->parent->getSubject();
                $record = $record[0];

                if ($isSingle) {
                    $record->{$alias} = empty ($records) ? null : $records[0];
                } else {
                    if (empty ($records)) {
                        $record->{$alias} = null;
                        $record->{$alias} = array ();
                    } else {
                        $record->{$alias} = $records;

                        if (static::$isPhalcon2) {
                            $record->{$alias} = null;
                            $record->{$alias} = $records;
                        }
                    }
                }
            } else {
                $indexedRecords = array ();

                // Keep all records in memory
                foreach ($builder->getQuery()->execute() as $record) {
                    $records[] = $record;

                    if ($isSingle) {
                        $indexedRecords[$record->readAttribute($relReferencedField)] = $record;
                    } else {
                        $indexedRecords[$record->readAttribute($relReferencedField)][] = $record;
                    }
                }

                foreach ($this->parent->getSubject() as $record) {
                    $referencedFieldValue = $record->readAttribute($relField);

                    if (isset ($indexedRecords[$referencedFieldValue])) {
                        $record->{$alias} = $indexedRecords[$referencedFieldValue];

                        if (static::$isPhalcon2 && is_array($indexedRecords[$referencedFieldValue])) {
                            $record->{$alias} = null;
                            $record->{$alias} = $indexedRecords[$referencedFieldValue];
                        }
                    } else {
                        $record->{$alias} = null;
                        
                        if (! $isSingle) {
                            $record->{$alias} = array ();
                        }
                    }
                }
            }
        }

        $this->subject = $records;

        return $this;
    }
}
