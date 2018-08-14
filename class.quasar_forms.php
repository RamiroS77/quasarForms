<?php

class quasarForm {
	
	public $formName='qform';
	public $token='';
	public $fields=array();
	public $errors=array();
	public $fieldNames=array();
	public $fieldsHTML=array();

	
	// Field properties
	
	// id = id for field
	// name = name for the field (for arrays [] will be use as sufix)
	// qfType =
	//		text = a text field
	//		hidden = hidden field
	//		system = will not be added to the code only kept as variable
	//		checkbox = checkbox field
	//		radio = radio field
	//		password = password field
	//		textarea = field for large amounts of text
	//		selectbox = list field
	//		button = buttons
	// values = all possible values
	// value = selected / default value(s)
	//		NOW = current date
	// status = enable / disabled
	// array=true | false (for fields that need to be shown multiple times)
	// getDataFrom = PHP function used to get the values. Array can be used for parameters. Example: 'getDataFrom'=>array('functionname',array('arg1','arg2'))

	function __construct($array) {
		foreach ($array as $fieldName=>$fieldData) {
			if (isset($fieldData['qfType'])) { // Ignore if there is no type defined
				$this->fieldNames[]=$fieldName;
				$this->fields[$fieldName]=array();
				$this->fields[$fieldName]=$fieldData;
			}
		}
	}
	
	function formatValue($mixed) {
		$ret=$mixed;
		
		if ($mixed==='NOW') {
			$ret=date('Y-m-d');
		}
		
		return $ret;
	}
	
	function repeatIdName($id, $name, $repeat) {
		$ret=array();
		$newId=$id;
		$newName=$name;
		for ($i=0; $i<$repeat; $i++) {
			if ($repeat>1) {
				$newId=$id.'-'.$i;
				$newName=$name.'[]';
			}
			$ret[$newId]=$newName;
		}
		return $ret;
	}
	
	function processFields($field='') {
		
		$currentField=$field;
		
		foreach ($this->fieldNames as $fieldName) {

			$id='';
			$newId='';
			$status='';
			$name='';
			$newName='';
			$index=array();
			$repeat=1;
			$value='';
			$values=array();
			$build='';
			$onlyItems=false;
			
			if ($field=='') {
				$currentField=$fieldName;
			}
			
			if ($fieldName==$currentField) {
			
				$data=$this->fields[$fieldName];
								
				// General for all field types
					
				if (isset($data['status'])) {
					if ($data['status']===false) {
						$status=' disabled';
					}
				}
				
				// Name
				
				if (isset($data['name'])) {
					$name=$data['name'];
				}else{
					$name=$fieldName;
				}
				
				// ID
				
				if (isset($data['id'])) {
					$id=$data['id'];
				}else{
					$id=$name;
				}
				
				
				// for fields that will be cloned or repeated

				if (isset($data['repeat']) && $data['repeat']>1) {
					$repeat=$data['repeat'];
				}			
				
				// Class
				
				$classArray= array(
					'form-control',
					'form-control-sm'
				);
				
				if (isset($data['class']) && sizeof($data['class'])>0) {
					foreach ($data['class'] as $aClass) {
						$classArray[]=$aClass;
					}
				}
				
				$class="class=\"".implode(" ", $classArray)."\"";
				
				// Label
				
				if (isset($data['label'])) {
					$build.="<label for=\"".$id."\" class=\"qfLabel\">".$data['label']."</label>";				
				}
				
				// Specifics for each field type

				switch ($data['qfType']) {
					case 'text':
						
						// Value
						if (isset($data['value'])) {
							$value=$this->formatValue($data['value']);
						}
						
						// Name
						if (!isset($data['name'])) {
							$name='';
						}else{
							$name=$data['name'];
						}				
						
						$index=$this->repeatIdName($id, $name, $repeat);
						foreach ($index as $newId=>$newName) {
							$this->fieldsHtml[$newId]=$build."<input type=\"text\" id=\"".$newId."\" name=\"".$newName."\" value=\"".$value."\" ".$status." ".$class." />\n";
						}
					break;
					
					case 'hidden':
						// Value
						if (!isset($data['value'])) {
							$value='';
						}else{
							$value=$data['value'];
						}
						$index=$this->repeatIdName($id, $name, $repeat);
						foreach ($index as $newId=>$newName) {
							$this->fieldsHtml[$newId]=$build."<input type=\"hidden\" id=\"".$newId."\" name=\"".$newName."\" value=\"".$value."\" ".$status." ".$class." />\n";
						}								
					break;

					case 'button':
						// Value
						if (!isset($data['value'])) {
							$value='';
						}else{
							$value=$data['value'];
						}
						$index=$this->repeatIdName($id, $name, $repeat);
						foreach ($index as $newId=>$newName) {
							$this->fieldsHtml[$newId]=$build."<input type=\"button\" id=\"".$newId."\" name=\"".$newName."\" value=\"".$value."\" ".$status." ".$class." />\n";
						}
					break;
					
					case 'selectbox':
						if (isset($data['value'])) {
							$value=$data['value'];
						}else{
							$value=array();
						}
					
						if (isset($data['values'])) {
							$values=$data['values'];
						}
						
						if (isset($data['onlyItems']) && ($data['onlyItems'])===true) {
							$onlyItems=true;
						}
						
						if (isset($data['getDataFrom']) && is_array($data['getDataFrom']) && sizeof($data['getDataFrom'])>0 ) {
							$functionName=$data['getDataFrom'][0];
							$args=$data['getDataFrom'][1];
							$values=call_user_func_array($functionName, $args);						
						}
						$index=$this->repeatIdName($id, $name, $repeat);
						foreach ($index as $newId=>$newName) {
							
							if (!$onlyItems) { 
								$build.="<select name=\"".$newName."\" id=\"".$newId."\" ".$class." >\n";
							}
							foreach ($values as $k=>$v) {
								$selected='';

								if (in_array($k, $value)) {
									$selected='selected';
								}
								$build.="<option value=\"".$k."\" ".$selected.">".$v."</option>";
								
							}						
							if (!$onlyItems) { 
								$build.="</select>\n";
							}
							$this->fieldsHtml[$newId]=$build;		
						}
						
						
					break;
					
				}
			}
		}
	}
	


}
?>