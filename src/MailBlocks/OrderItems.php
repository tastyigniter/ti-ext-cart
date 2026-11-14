<?php

declare(strict_types=1);

namespace Igniter\Cart\MailBlocks;

use Igniter\System\Classes\MailBuilder\BaseMailBlock;
use Igniter\System\Classes\MailBuilder\BladeFragment;
use Igniter\System\Classes\MailBuilder\BladeWriter;
use Igniter\System\Classes\MailBuilder\CompileContext;
use Igniter\System\Classes\MailBuilder\TextWriter;
use Override;

/**
 * Mail builder block listing the ordered menu items and the order totals.
 * Offered for templates that may use the order variables.
 */
class OrderItems extends BaseMailBlock
{
    public function blockDetails(): array
    {
        return [
            'code' => 'igniter.cart::order_items',
            'name' => 'igniter.cart::default.mail_blocks.text_order_items',
            'description' => 'igniter.cart::default.mail_blocks.help_order_items',
            'icon' => 'fa fa-receipt',
            'category' => 'data',
            'modes' => [CompileContext::MODE_TEMPLATE],
        ];
    }

    #[Override]
    public function defineFields(): array
    {
        return [
            'label_name' => [
                'label' => 'igniter.cart::default.mail_blocks.label_column_name',
                'type' => 'text',
                'default' => lang('igniter.cart::default.mail_blocks.text_column_name'),
                'validationRule' => ['nullable', 'string', 'max:64'],
            ],
            'label_price' => [
                'label' => 'igniter.cart::default.mail_blocks.label_column_price',
                'type' => 'text',
                'span' => 'left',
                'default' => lang('igniter.cart::default.mail_blocks.text_column_price'),
                'validationRule' => ['nullable', 'string', 'max:64'],
            ],
            'label_subtotal' => [
                'label' => 'igniter.cart::default.mail_blocks.label_column_subtotal',
                'type' => 'text',
                'span' => 'right',
                'default' => lang('igniter.cart::default.mail_blocks.text_column_subtotal'),
                'validationRule' => ['nullable', 'string', 'max:64'],
            ],
            'show_options' => [
                'label' => 'igniter.cart::default.mail_blocks.label_show_options',
                'type' => 'switch',
                'default' => true,
            ],
            'show_comments' => [
                'label' => 'igniter.cart::default.mail_blocks.label_show_comments',
                'type' => 'switch',
                'default' => true,
            ],
            'show_totals' => [
                'label' => 'igniter.cart::default.mail_blocks.label_show_totals',
                'type' => 'switch',
                'default' => true,
            ],
        ];
    }

    #[Override]
    public function appliesTo(CompileContext $context): bool
    {
        return $context->variables->isArray('order_menus')
            && $context->variables->hasItemField('order_menus', 'menu_name');
    }

    /**
     * Recognises the order items table of the registered order mail views.
     */
    #[Override]
    public function importChunk(string $chunk, CompileContext $context): ?array
    {
        if (!$this->appliesTo($context) || !str_starts_with($chunk, "@partial('table')")
            || !preg_match('/@foreach\s*\(\s*\$order_menus\s+as\s+\$order_menu\s*\)/', $chunk)
        ) {
            return null;
        }

        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $chunk, $headings);
        [$name, $price, $subtotal] = array_pad(array_map(fn(string $heading): string => trim(strip_tags($heading)), $headings[1]), 3, null);

        return array_filter([
            'label_name' => $name,
            'label_price' => $price,
            'label_subtotal' => $subtotal,
        ], fn(?string $label): bool => !is_null($label)) + [
            'show_options' => str_contains($chunk, "['menu_options']"),
            'show_comments' => str_contains($chunk, "['menu_comment']"),
            'show_totals' => str_contains($chunk, '$order_totals'),
        ];
    }

    public function toBlade(array $settings, BladeWriter $writer, CompileContext $context): BladeFragment
    {
        if (!$this->appliesTo($context)) {
            return $writer->none();
        }

        $header = $writer->tag('thead', [], $writer->tag('tr', [], $writer->join(
            $writer->tag('th', ['width' => '50%', 'align' => 'left'], $writer->text($settings['label_name'] ?? '')),
            $writer->tag('th', ['align' => 'right'], $writer->text($settings['label_price'] ?? '')),
            $writer->tag('th', ['align' => 'right'], $writer->text($settings['label_subtotal'] ?? '')),
        )));

        $rows = $writer->loop('order_menus', function(BladeWriter $writer, string $item) use ($settings, $context): BladeFragment {
            $name = $writer->join(
                $this->field($writer, $context, 'order_menus', $item, 'menu_quantity'),
                $writer->static(' x '),
                $writer->item($item, 'menu_name'),
            );

            foreach (['show_options' => 'menu_options', 'show_comments' => 'menu_comment'] as $setting => $field) {
                if (!empty($settings[$setting]) && $context->variables->hasItemField('order_menus', $field)) {
                    $name = $writer->join($name, $writer->static('<br>'), $writer->item($item, $field));
                }
            }

            return $writer->tag('tr', [], $writer->join(
                $writer->tag('td', [], $name),
                $writer->tag('td', ['align' => 'right'], $this->field($writer, $context, 'order_menus', $item, 'menu_price')),
                $writer->tag('td', ['align' => 'right'], $this->field($writer, $context, 'order_menus', $item, 'menu_subtotal')),
            ));
        });

        if (!empty($settings['show_totals']) && $context->variables->isArray('order_totals')) {
            $rows = $writer->join(
                $rows,
                $writer->static('<tr><td colspan="99"><hr></td></tr>'),
                $writer->loop('order_totals', fn(BladeWriter $writer, string $item): BladeFragment => $writer->tag('tr', [], $writer->join(
                    $writer->tag('td', [], $writer->static('&nbsp;')),
                    $writer->tag('td', ['align' => 'right'], $this->field($writer, $context, 'order_totals', $item, 'order_total_title')),
                    $writer->tag('td', ['align' => 'right'], $this->field($writer, $context, 'order_totals', $item, 'order_total_value')),
                ))),
            );
        }

        $table = $writer->tag('table', ['border' => '0', 'cellpadding' => '0', 'cellspacing' => '0', 'width' => '100%'],
            $writer->join($header, $writer->tag('tbody', [], $rows)),
        );

        return $context->allowsPartial('table') ? $writer->partial('table', [], $table) : $table;
    }

    #[Override]
    public function toText(array $settings, TextWriter $writer, CompileContext $context): BladeFragment
    {
        if (!$this->appliesTo($context)) {
            return $writer->none();
        }

        $lines = $writer->loop('order_menus', fn(BladeWriter $writer, string $item): BladeFragment => $writer->join(
            $this->field($writer, $context, 'order_menus', $item, 'menu_quantity'),
            $writer->static(' x '),
            $writer->item($item, 'menu_name'),
            $writer->static(' '),
            $this->field($writer, $context, 'order_menus', $item, 'menu_subtotal'),
            $writer->static("\n"),
        ));

        if (!empty($settings['show_totals']) && $context->variables->isArray('order_totals')) {
            $lines = $writer->join($lines, $writer->loop('order_totals', fn(BladeWriter $writer, string $item): BladeFragment => $writer->join(
                $this->field($writer, $context, 'order_totals', $item, 'order_total_title'),
                $writer->static(': '),
                $this->field($writer, $context, 'order_totals', $item, 'order_total_value'),
                $writer->static("\n"),
            )));
        }

        return $lines;
    }

    protected function field(BladeWriter $writer, CompileContext $context, string $array, string $item, string $field): BladeFragment
    {
        return $context->variables->hasItemField($array, $field) ? $writer->item($item, $field) : $writer->none();
    }
}
