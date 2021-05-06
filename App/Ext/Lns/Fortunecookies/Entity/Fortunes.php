<?php

namespace Lns\Fortunecookies\Entity;

class Fortunes extends \Lns\Sb\Lib\Entity\ClassOverride\OfDbEntity
{

    /* 
   *   declare all columns of your user table
   *   all comlumns that are not in this COLUMNS array, are not editable
   */
    const COLUMNS = [
        'id',
        'message',
    ];


    /* 
   *   declare the tablename.
   *   your table might have prefix like ab_ 
   *   therefore the table name will be ab_user
   *   in opoink you just have to declare the name without prefix
   *   opoink will automatically add prefix for this
   */
    protected $tablename = 'fortunes';

    /* 
   *   declare the table's primary key
   */
    protected $primaryKey = 'id';

    public function addFortune($param)
    {

        foreach ($param as $key => $value) {
           if (in_array($key, self::COLUMNS) && isset($value)) {
              $this->setData($key, $value);
           }
        }
        return $this->__save();
    }

    public function fortuneListing($param){
        $limit = 10;

        if (isset($param['limit'])) {
            if ($param['limit'] > 1) {
               $limit = $param['limit'];
            }
        }

         return $this->getFinalResponse($limit);
    }

}
