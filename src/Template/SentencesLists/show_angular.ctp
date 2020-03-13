<?php
use App\Model\CurrentUser;
use App\Model\Entity\SentencesList;

$this->Html->script('/js/sentences_lists/show.ctrl.js', ['block' => 'scriptBottom']);

$listCount = $this->Paginator->param('count');
$listId = $list['id'];
$listVisibility = $list['visibility'];
$listName = h($list['name']);

$listData = [
    'id' => $list['id'],
    'name' => $list['name']
];
$listJSON = htmlspecialchars(json_encode($listData), ENT_QUOTES, 'UTF-8');

$this->set('title_for_layout', $this->Pages->formatTitle($listName));
?>

<div id="annexe_content">
    <?php $this->Lists->displayListsLinks(); ?>

    <div class="section md-whiteframe-1dp">
        <h2><?php echo __('About this list'); ?></h2>
        <?php
        $linkToAuthorProfile = $this->Html->link(
            $user['username'],
            array(
                'controller' => 'user',
                'action' => 'profile',
                $user['username']
            )
        );
        $createdBy = format(
            __('created by {listAuthor}'),
            array('listAuthor' => $linkToAuthorProfile)
        );
        $createdDate = $this->Date->ago($list['created']);
        echo $this->Html->tag('p', $createdBy);
        echo $this->Html->tag('p', $createdDate);
        $numberOfSentencesMsg = format(
            __n(
                /* @translators: number of sentences contained in the list */
                'Contains {n}&nbsp;sentence',
                'Contains {n}&nbsp;sentences',
                $listCount,
                true
            ),
            array('n' => $this->Number->format($listCount))
        );
        echo $this->Html->tag('p', $numberOfSentencesMsg);
        ?>
    </div>


    <?php
    if ($permissions['canEdit']) {
        ?>
        <div class="section md-whiteframe-1dp">
            <h2><?php echo __('Options'); ?></h2>
            <ul class="sentencesListActions">
                <?php
                echo '<p>';
                $this->Lists->displayVisibilityOption($listId, $listVisibility);
                echo '</p>';
                echo '<p>';
                $this->Lists->displayEditableByOptions($listId, $list['editable_by']);
                echo '</p>';
                ?>
            </ul>
        </div>
        <?php
    }
    ?>

    <div class="section md-whiteframe-1dp">
    <h2><?php echo __('Actions'); ?></h2>
    <?php
    $this->Lists->displayTranslationsDropdown($listId, $translationsLang);
    ?>
    <div layout="column" layout-align="end center">
        <?php
        if ($permissions['canEdit']) {
            $this->Lists->displayDeleteButton($listId);
        }

        $this->Lists->displayDownloadLink($listId);
        ?>
    </div>
    </div>

</div>

<div id="main_content">

<section class="md-whiteframe-1dp" ng-controller="SentencesListsShowController as vm" ng-init="vm.initList(<?= $listJSON ?>, sentenceForm)">
    <md-toolbar class="md-hue-2">
        <div class="md-toolbar-tools">
            <h2 ng-cloak flex>{{vm.list.currentName}}</h2>

            <?= $this->element('sentences/expand_all_menus_button'); ?>
            
            <?php if ($permissions['canEdit']) { ?>
            <md-button class="md-icon-button" ng-cloak ng-click="vm.editName()">
                <md-icon>edit
                    <md-tooltip><?= __('Edit name') ?></md-tooltip>
                </md-icon>
            </md-button>
            <?php } ?>

            <?php if ($permissions['canAddSentences']) { ?>
            <md-button class="md-icon-button" ng-click="vm.showForm = true" ng-cloak>
                <md-icon>add
                    <md-tooltip><?= __('Add sentences'); ?></md-tooltip>
                </md-icon>
            </md-button>
            <?php } ?>
        </div>
    </md-toolbar>

    <?php if ($permissions['canEdit']) { ?>
        <form id="list-name-form" layout="column" ng-if="vm.showEditNameForm" ng-cloak>
            <md-input-container>
                <label><?= __('List name'); ?></label>
                <input id="edit-name-input" ng-model="vm.list.name" ng-enter="vm.saveListName()" ng-escape="vm.showEditNameForm = false"></input>
            </md-input-container>
            
            <div layout="row" layout-align="end">
                <md-button class="md-raised" ng-click="vm.showEditNameForm = false">
                    <?= __('Cancel') ?>
                </md-button>
                <md-button class="md-raised md-primary" ng-click="vm.saveListName()">
                    <?= __('Save') ?>
                </md-button>
            </div>
        </form>
    <?php } ?>

    <?php
    if ($permissions['canAddSentences']) {
        ?>
        <div ng-if="vm.showForm" ng-cloak>
            <?php echo $this->element('sentences/add_sentence_form', [
                'withCloseButton' => true
            ]); ?>
        </div>
        <?php
    }
    ?>
    
    <md-progress-linear ng-if="vm.inProgress"></md-progress-linear>

    <md-content ng-cloak>
    <?php
    if ($permissions['canAddSentences'] && count($sentencesInList) == 0) {
        ?>
        <div class="no-sentence-info" ng-if="!vm.showForm && vm.sentences.length === 0">
            <p><?= __('This list is empty.') ?></p>
            <div class="hint">
                <?php
                echo format(
                    __(
                        'You can create new sentences directly in this list by clicking on the '.
                        '{addSentenceButton} icon in the header of this section.', true
                    ),
                    ['addSentenceButton' => '<md-icon>add</md-icon>']
                );
                ?>
            </div>
            <div class="hint">
                <?php
                echo format(
                    __(
                        'You can also add existing sentences to this list, from other pages, by clicking on '.
                        'the {addToListButton} icon in the menu of the sentence.', true
                    ),
                    ['addToListButton' => '<md-icon>list</md-icon>']
                );
                ?>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="sortBy" id="sortBy">
        <strong><?php echo __("Sort by:") ?> </strong>
        <?php
        echo $this->Paginator->sort('created', __('date added to list'));
        echo ' | ';
        echo $this->Paginator->sort('sentence_id', __('date created'));
        ?>
        </div>
        <?php
    }
    ?>
    
    
    <?php $this->Pagination->display(); ?>

    <?php
    if ($permissions['canAddSentences']) {
        echo $this->element('sentences_lists/sentence_in_list', [
            'sentenceAndTranslationsParams' => [
                'sentenceData' => 'sentence',
                'directTranslationsData' => 'sentence.translations[0]',
                'indirectTranslationsData' => 'sentence.translations[1]',
                'duplicateWarning' => __('The sentence you tried to create already exists. The existing sentence was added to your list instead.')
            ],
            'ngRepeat' => 'sentence in vm.sentences',
            'canRemove' => $permissions['canRemoveSentences']
        ]);
    }

    foreach ($sentencesInList as $item) {
        $sentence = $item->sentence;
        echo $this->element('sentences_lists/sentence_in_list', [
            'sentenceAndTranslationsParams' => [
                'sentence' => $sentence,
                'translations' => $sentence->translations,
                'user' => $sentence->user
            ],
            'sentenceId' => $sentence->id,
            'canRemove' => CurrentUser::isMember() && $permissions['canRemoveSentences']
        ]);
    }
    ?>
    
    <?php $this->Pagination->display(); ?>

    </md-content>
</div>
