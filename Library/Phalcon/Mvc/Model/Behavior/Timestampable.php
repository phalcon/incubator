<?php

namespace Phalcon\Model\Behavior;

class Timestampable extends \Phalcon\Mvc\Model\Behavior
{
	protected $_defaults = array(
		'updateField' => 'updated_at',
		'createField' => 'created_at'
	);

	public function beforeValidationOnCreate ( $event, $model )
	{
		$options = array_merge($this->getOptions(), $this->_defaults);
		$created = $model->readAttribute( $options['createField'] );

		if ( null === $created ) {
			$model->{$options['createField']} = new \Phalcon\Db\RawValue( 'NOW()' );
		}
	}

	public function beforeValidationOnUpdate ( $event, $model )
	{
		$options = array_merge($this->getOptions(), $this->_defaults);
		$updated = $model->readAttribute( $options['updateField'] );

		if ( null === $updated ) {
			$model->{$options['updateField']} = new \Phalcon\Db\RawValue( 'NOW()' );
		}
	}
}
