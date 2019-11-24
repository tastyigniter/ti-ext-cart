<div class="d-sm-flex">
    <?php if ($location->hasMedia('thumb')) { ?>
        <div class="w-sm-25 d-none d-sm-block mr-sm-4">
            <img
                class="img-responsive"
                src="<?= $location->getThumb(); ?>"
            >
        </div>
    <?php } ?>

    <div class="">
        <dl class="no-spacing mb-0">
            <dd><h2 class="h4 mb-0 font-weight-normal"><?= $location->location_name; ?></h2></dd>
            <dd>
                <span class="text-muted text-truncate"><?= format_address($location->getAddress(), FALSE); ?></span>
            </dd>
            <dd><?= $location->location_telephone; ?></dd>
            <dd>
                <a
                    href="<?= page_url('local/menus', ['location' => $location->permalink_slug]); ?>"
                ><?= lang('main::lang.menu_menu'); ?></a>
            </dd>
        </dl>
    </div>

</div>