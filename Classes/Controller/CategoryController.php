<?php
namespace OliverKlee\Seminars\Controller;

/**
 * This file is part of the "seminars" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * @author Andrea Schmuttermair <andrea@schmutt.de>
 */


/**
 * Controller of news records
 *
 */
class CategoryController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Tx_Seminars_Mapper_Category
     */
    protected $categoryRepository;


    /**
     * @param \Tx_Seminars_Mapper_Category $categoryRepository
     */
    public function injectCategoryRepository(\Tx_Seminars_Mapper_Category $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function initializeAction() {
        if (isset($this->settings['flexforms']) && is_array($this->settings['flexforms'])) {
            $flexformsSettings = $this->settings['flexforms'];
            //unset($this->settings['flexforms']);
            foreach ($flexformsSettings as $flexformKey => $flexformValue){
                if ((strlen($flexformValue) > 0 || (int)$flexformValue > 0)) {
                    $this->settings[$flexformKey] = $flexformValue;
                }
            }
        }
    }


    /**
     * action list
     *
     * @return void
     */
    public function listAction() {

        $categories = $this->categoryRepository->findBySettings($this->settings);

        $this->view->assign('categories', $categories);

    }



}
