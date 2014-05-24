<?php
namespace Phalcon\Mvc\Model\Behavior;

/**
 * DateTime behavior.
 * Enables models to use instances of \DateTime objects for db datetime fields.
 * NOTICE: Tested only with MySQL.
 *
 * Expects array as constructor argument with field name(s) as key(s) and options array as value.
 * Options available are:
 * - $options['timezone'] (optional, timezone identifier string)
 * - $options['className'] (optional, user-defined DateTime instance)
 *
 * Example usage:
 * <code>
 * $this->addBehavior(
 *     new \Phalcon\Mvc\Model\Behavior\DateTime(array(
 *          'createdAt' => $options,
 *          'updatedAt' => $options
 *     ));
 * );
 * </code>
 *
 *
 * @package Phalcon\Mvc\Model\Behavior
 */
class DateTime extends \Phalcon\Mvc\Model\Behavior
{
    /**
     * Events which this behavior accepts.
     *
     * @var array
     */
    protected $acceptedEvents = array('validation', 'afterCreate', 'afterUpdate');

    /**
     * Does conversion from and to \DateTime object for given field and options.
     *
     * @param string             $eventType type of executed event,
     *                                      accepts only "afterValidation" and "afterSave"
     *
     * @param \Phalcon\Mvc\Model $model     Model instance which implements this behavior
     *
     * @return bool|void false on invalid event
     */
    public function notify($eventType, $model)
    {
        if (!in_array($eventType, $this->acceptedEvents)) {
            return;
        }

        foreach ($this->getOptions() as $field => $options) {
            // skip, no data
            if (!$model->readAttribute($field)) {
                continue;
            }

            // get className option
            $className = 'DateTime';
            if (array_key_exists('className', $options)) {
                $className = $options['className'];
            }

            // get timezone option
            $timezone = date_default_timezone_get();
            if (isset($options['timezone'])) {
                $timezone = $options['timezone'];
            }

            // convert to datetime string for timestamp db field
            if ($eventType === 'validation') {
                $this->onValidation($model, $field);
            }

            // restore back to DateTime object
            // using afterCreate and afterUpdate events because they happen earlier then afterSave event
            if (in_array($eventType, array('afterCreate', 'afterUpdate'))) {
                $this->onAfterSave($model, $field, $timezone, $className);
            }
        }

        return true;
    }

    /**
     * Executed on "validation" event.
     * Converts \DateTime field object to timestamp string representation for db storing.
     *
     * @param \Phalcon\Mvc\Model $model model which implements this behavior
     * @param string             $field field which contains \DateTime object
     *
     * @return void
     *
     * @throws \Phalcon\Mvc\Model\Behavior\Exception in case field contains object which doesn't extend from \DateTime
     */
    protected function onValidation(\Phalcon\Mvc\Model $model, $field)
    {
        if (!$model->readAttribute($field) instanceof \DateTime) {
            $type = gettype($model->readAttribute($field));
            if ($type === 'object') {
                $type = get_class($model->readAttribute($field));
            }
            throw new \Phalcon\Mvc\Model\Behavior\Exception(
                sprintf(
                    'Property "%s" must be instance of DateTime object. "%s" given.',
                    $field,
                    $type
                )
            );
        }
        $model->writeAttribute($field, $model->readAttribute($field)->format('Y-m-d H:i:s'));
    }

    /**
     * Executed on "afterUpdate" or "afterCreate" (before "afterSave").
     * Purpose of this method is to restore \DateTime object back from string representation.
     *
     * @param \Phalcon\Mvc\Model $model     model which implements this behavior
     * @param string             $field     field which contains datetime string
     * @param string             $timezone  timezone string
     * @param string             $className name of class on which "createFromFormat" method should be executed
     *
     * @return void
     */
    protected function onAfterSave(\Phalcon\Mvc\Model $model, $field, $timezone, $className)
    {
        $model->writeAttribute(
            $field,
            $className::createFromFormat(
                'Y-m-d H:i:s',
                $model->readAttribute($field),
                new \DateTimeZone($timezone)
            )
        );
    }
}
