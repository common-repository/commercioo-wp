<?php
namespace Commercioo\Update;
class commercioo_do_update{
    public static function update_database_changelog($hook='',$item_name=''){
        if(!empty($item_name)){
            if(has_action($hook)){
                do_action($hook,$item_name);
            }
        }
    }
}


