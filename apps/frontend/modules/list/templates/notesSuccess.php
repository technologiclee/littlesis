<?php use_helper('Date') ?>


<?php include_partial('list/header', array('list' => $list, 'show_actions' => true)) ?>


<?php include_partial('note/formteaser', array('record' => $list)) ?>


<?php include_partial('global/section', array(
  'title' => $sf_request->getParameter('user_id') ? 'Your Notes' : 'Notes',
  'pager' => $note_pager,
  'pointer' => ($sf_request->getParameter('user_id') ? 'Your notes' : 'Analyst notes') . ' about ' . $list->name,
  'action' => !$sf_user->isAuthenticated() ? null : array(
    'text' => $sf_request->getParameter('user_id') ? 'all notes' : 'your notes',
    'url' => $list->getInternalUrl('notes', array('user_id' => $sf_request->getParameter('user_id') ? null : $sf_user->getGuardUser()->id))
   )
)) ?>

<?php foreach ($note_pager->execute() as $note) : ?>
  <?php include_partial('note/full', array('note' => $note)) ?>
<?php endforeach; ?>