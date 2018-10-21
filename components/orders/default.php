<?php if ($orderIdParam) { ?>
    <?= partial('@preview') ?>
<?php } else { ?>
    <?= partial('@list') ?>
<?php } ?>
