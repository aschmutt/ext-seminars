<?php

/**
 * This is a helper class - functions will be moved to other classes or Oelib Extension
 *
 * @author Andrea Schmuttermair <andrea@schmutt.de>
 */
class Tx_Seminars_Pi3Helper
{

    /**
     * @todo: \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode()
     * @param string $commaSeparatedList
     * @return array
     */
    public static function getUidArrayFromCommaSeparatedList($commaSeparatedList)
    {
        $result = array();

        $inputArray = explode(',', $commaSeparatedList);

        foreach ($inputArray as $value) {
            $intValue = (int)$value;
            if ($intValue > 0) {
                $result[] = $intValue;
            }

        }
        return $result;
    }

    /**
     * @todo: add to oelib
     *
     * @param string $tablename
     * @param string $fieldname
     * @param int $uid
     * @return array of FAL Entries
     */
    public static function loadFalFiles($tablename, $fieldname, $uid)
    {
        /*Ersetzen durch Core Funktion
         * \TYPO3\CMS\Core\Resource\FileRepository


        findByRelation
        */

        /**@var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_reference');

        //
        $result = $queryBuilder
            ->select('sys_file.uid')
            ->from('sys_file_reference')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->like('sys_file_reference.tablenames', $queryBuilder->createNamedParameter($tablename)),
                $queryBuilder->expr()->like('sys_file_reference.fieldname', $queryBuilder->createNamedParameter($fieldname )),
                $queryBuilder->expr()->eq('sys_file_reference.uid_foreign', (int)$uid),
                $queryBuilder->expr()->eq('sys_file_reference.uid_local', 'sys_file.uid')
            )
            ->orderBy('sys_file_reference.sorting_foreign')
            ->execute()
            ->fetchAll();

        $output = [];
        /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory */
        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        foreach ($result as $item) {
            try {
                $file = $resourceFactory->getFileObject($item['uid']);
                $output[] = $file;
            }
            catch (Exception $e) {
            }
        }

        return $output;
    }

}