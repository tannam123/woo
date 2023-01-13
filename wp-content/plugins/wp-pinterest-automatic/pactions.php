<?php 
 function my_custom_bulk_actions($actions){
 	
 	 
        $action['pin']='pin';
        return $actions;
    }
    add_filter('bulk_actions-users','my_custom_bulk_actions');
?>