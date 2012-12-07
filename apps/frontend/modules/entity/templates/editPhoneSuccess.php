<?php include_partial('entity/header', array('entity' => $entity)) ?>

<h2>Edit Phone</h2>

<?php include_partial('global/formerrors', array('form' => array($phone_form, $reference_form))) ?>

<form action="<?php echo url_for('entity/editPhone') ?>" method="POST">
<?php echo input_hidden_tag('id', $sf_request->getParameter('id')) ?>

<table>
  <?php include_partial('reference/required', array('form' => $reference_form)) ?>

  <?php include_partial('global/form', array('form' => $phone_form)) ?>
  <tr>
    <td></td>
    <td>
      <?php echo submit_tag('Save') ?>
      <?php echo button_to('Cancel', $entity->getInternalUrl('editContact')) ?>
    </td>
  </tr>
</table>

</form>