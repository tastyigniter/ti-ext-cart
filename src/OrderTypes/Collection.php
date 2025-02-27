<?php

declare(strict_types=1);

namespace Igniter\Cart\OrderTypes;

use Override;
use Igniter\Cart\Classes\AbstractOrderType;
use Igniter\Local\Facades\Location as LocationFacade;

class Collection extends AbstractOrderType
{
    #[Override]
    public function getOpenDescription(): string
    {
        return sprintf(
            lang('igniter.cart::default.text_collection_time_info'),
            sprintf(lang('igniter.local::default.text_in_minutes'), $this->getLeadTime())
        );
    }

    #[Override]
    public function getOpeningDescription(string $format): string
    {
        $starts = make_carbon($this->getSchedule()->getOpenTime());

        return sprintf(
            lang('igniter.cart::default.text_collection_time_info'),
            sprintf(lang('igniter.local::default.text_starts'), '<b>'.$starts->isoFormat($format).'</b>')
        );
    }

    #[Override]
    public function getClosedDescription(): string
    {
        return sprintf(
            lang('igniter.cart::default.text_collection_time_info'),
            lang('igniter.local::default.text_is_closed')
        );
    }

    #[Override]
    public function getDisabledDescription(): string
    {
        return lang('igniter.cart::default.text_collection_is_disabled');
    }

    #[Override]
    public function isActive(): bool
    {
        return $this->code === LocationFacade::orderType();
    }

    #[Override]
    public function isDisabled(): bool
    {
        return !$this->location->hasCollection();
    }
}
