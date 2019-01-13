<?php

/**
 * This class represents a mapper for categories.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Andrea Schmuttermair <andrea@schmutt.de>
 */
class Tx_Seminars_Mapper_Category extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_categories';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Category::class;


    /**
     * @return \Tx_Oelib_List the \Tx_Oelib_List<Tx_Seminars_Model_Category>
     */
    public function findBySettings($settings) {
        $whereClause = ' 1=1 ';

        //Categories
        if (strlen($settings['limitListViewToCategories']) > 0) {
            //removes anything not a integer uid
            $limitCategories = \Tx_Seminars_Pi3Helper::getUidArrayFromCommaSeparatedList($settings['limitListViewToCategories']);
            $whereClause .= ' AND uid IN ( ' . implode(',', $limitCategories) . ' ) ';
        }

        return $this->findByWhereClause($whereClause, 'sorting');
    }

}
