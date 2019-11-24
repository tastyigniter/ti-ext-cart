<div class="card mb-1">
    <div class="card-body text-sm-center">
        <?= partial('@status') ?>
    </div>
</div>

<?php if (!$session->customer()) { ?>
    <div class="card mb-1">
        <div class="card-body text-sm-center">
            <a
                href="<?= $session->loginUrl(); ?>"
            ><?= lang('igniter.cart::default.orders.text_login_to_view_more') ?></a>
        </div>
    </div>
<?php } else { ?>
    <?php if ($showReviews AND !empty($reviewable)) { ?>
        <div class="card mb-1">
            <div class="card-body">
                <?= partial('localReview::form') ?>
            </div>
        </div>
    <?php } ?>

    <div class="row no-gutters">
        <div class="col-sm-7 pr-sm-1">
            <div class="card mb-1">
                <div class="card-body">
                    <?= partial($__SELF__.'::restaurant', ['location' => $order->location]) ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?= partial($__SELF__.'::items') ?>
                </div>
            </div>
        </div>

        <div class="col-sm-5">
            <?= partial($__SELF__.'::details') ?>
        </div>
    </div>
<?php } ?>