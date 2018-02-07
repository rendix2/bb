<?php

namespace App\AdminModule\Presenters;

/**
 * Description of CategoryPresenter
 *
 * @author rendi
 */
class CategoryPresenter extends Base\AdminPresenter {

    private $forumsManager;

    public function __construct(\App\Models\CategoriesManager $manager) {
        parent::__construct($manager);
    }

    public function injectForumsManager(\App\Models\ForumsManager $forumsManager) {
        $this->forumsManager = $forumsManager;
    }

    public function renderEdit($id = null) {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Param id is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item #' . $id . ' not found.');
            }

            $this['editForm']->setDefaults($item);

            $forums = $this->forumsManager->createForums($this->forumsManager->getForumsByCategoryId($id), 0);

            if (!$forums) {
                $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_WARNING);
            }

            $this->template->item = $item;
            $this->template->title = $this->getTitleOnEdit();
            $this->template->forums = $forums;
        } else {
            $this->template->title = $this->getTitleOnAdd();
            $this->template->forums = [];

            $this['editForm']->setDefaults([]);
        }
    }

    protected function createComponentEditForm() {
        $form = $this->getBootStrapForm();

        $form->setTranslator($this->getAdminTranslator());

        $form->addText('category_name', 'Category name:')->setRequired(true);
        $form->addCheckbox('category_active', 'Category active:');

        return $this->addSubmitB($form);
    }

}
