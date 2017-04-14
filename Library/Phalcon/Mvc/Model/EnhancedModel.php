<?php
use Phalcon\Mvc\Model;
    
abstract class EnhancedModel extends Model
{
	/* This is a method I propose adding to Model that takes a stdClass object that has been decoded from a JSON string.
	 * As a convention the user should create a Model object and call $my_model->promote($stdClass_mess) and the method will
	 * walk the object populating it with the user's data using property names as hints for the Model classes of relationships,
	 * and the $my_model class as the parent's type (assumes the top level stdClass object is of the $my_model type).
	 * Example $stdClass_mess:
	 
		stdClass Object ( [id] => 1 [users_id] => 2 [name] => TestBudget [cash] => 1985.23   <--promote() will assume this is the current Model's type
			[Bills] => Array (   <-- these property names, the values being an array, will be used to create the appropriate related Model objects (hints)
                        [0] => stdClass Object ( [id] => 1 [budgets_id] => 1 [name] => Car [amount] => 350.25 ) 
                     ) 
			[Expenses] => Array (   <-- these property names, the values being an array, will be used to create the appropriate related Model objects 
                        [0] => stdClass Object ( [id] => 1 [budgets_id]=> 1 [name] => Food [amount] => 600 ) 
                        [1] => stdClass Object ( [id] => 2 [budgets_id] => 1 [name] => Gas [amount] => 250 ) 
                        ) 
		)

	 *  This was designed to support one-to-many relationships and I will explore how this works with other relationships.
	 *
	 *  @author Sean K. Anderson <sean.anderson@datavirtue.com> 
	 */
	public function promote($std_class, $target_class=null){
		try{
			
			/*
			 * << Establish the parent Model type >>
			 * Allow the user to specify the target_class by providing a string, object. 
			 * Assume extending class when null.
			 */ 
			if (is_null($target_class)){	
				$object = $this;
			}elseif (is_object($target_class)){
				$object = $target_class;
			}elseif (is_string($target_class)){
				$object = new $target_class();  //New up a Model from a user-supplied class name--usually during recursion
			}else {
				return $std_class;  //Not playing nice?  Return the data passed in.
			}
			
			/* 
			 * Prepare this array in case we encounter an array of related records nested in our stdClass
			 * Each element will be an array of one or more...the children being instances of the related Model (related to the parent).
			 */
			 
			$related_entities = array();  //eventual array of arrays
			
			/* Loop through the stdClass, accessing properties like an array. */
			foreach ($std_class as $property => $value) {
				
				/* 
				 * If an array is found as the value of a property we assume it is full of realted entities; 
				 * with the property name being the Model type (case sensitive)
				 * 
				 */ 
				if (is_array($value)){  //all of these are stdClass as well, so we recurse to handle each one
										 
					/* 
					 * $property should be named to fit the model of the entities in the array 
					 * This is dependent on the user building the JSON object correctly upstream.
					 *  
					 */
					$related_entities[$property] = array(); 
					
					foreach($value as $entity){  //Get each array element and treat it as an entity
						/* 
						 * For thought-simplicity sake, let's assume this promote() call doesn't find related entities inside this related entity (Yo Dawg...). 
						 * This adds the related entity to an array named for its Model: $related_entities['related_model_name'] = $object_returned_from_promote(). 
						 * This WILL, of course, recurse to infinity building out the complete data model.
						 */
						 $related_entities[$property] = $this->promote($entity, $property); 
					}
									
				}else {
						/* Just add the value found to the property of the Model object */	
						$object->{$property} = $value;
				}			
			}		
			
			/*	
			 * Add each array of whatever related entities were found, to the parent object/table
			 * This depends on the Phalcon ORM Model convention: $MyTableObject->relatedSomthings = array_of_related_somethings
			 */
			foreach($related_entities as $related_model => $entity_data){
					$object->{$related_model} = $entity_data;		
			}
			 		
		}catch(Exception $e){
			/* 
			 * If the user supplied data (decoded JSON) that does not match the Model we are going to experience an exception
			 * when trying to access a property that doesn't exist. 
			 * 
			 */ 
			throw $e;
		}
		return $object;	/* Usually only important when we are using recursion. */
	}   
}
	