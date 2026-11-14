<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\MailBlocks;

use Igniter\Cart\MailBlocks\OrderItems;
use Igniter\Flame\Mail\MailParser;
use Igniter\Flame\Support\Facades\File;
use Igniter\System\Classes\MailBuilder\BladeWriter;
use Igniter\System\Classes\MailBuilder\CompileContext;
use Igniter\System\Classes\MailBuilder\MailBlockManager;
use Igniter\System\Classes\MailBuilder\MailBuilder;
use Igniter\System\Classes\MailBuilder\MailImporter;
use Igniter\System\Classes\MailBuilder\MailVariables;
use Igniter\System\Classes\MailBuilder\TextWriter;
use Illuminate\Support\Facades\View;

beforeEach(function(): void {
    $this->builder = resolve(MailBuilder::class);
    $this->context = $this->builder->makeContext(CompileContext::MODE_TEMPLATE, 'igniter.cart::mail.order');
    $this->block = new OrderItems;
});

it('is registered for order templates only', function(): void {
    $manager = resolve(MailBlockManager::class);

    expect($manager->findBlock('igniter.cart::order_items'))->toBeInstanceOf(OrderItems::class)
        ->and($manager->listBlocks($this->context))->toHaveKey('igniter.cart::order_items')
        ->and($manager->listBlocks($this->builder->makeContext(CompileContext::MODE_TEMPLATE, 'igniter.cart::mail.low_stock_alert')))
        ->not->toHaveKey('igniter.cart::order_items');
});

it('compiles the order items table', function(): void {
    $settings = $this->block->sanitize([]);
    $html = $this->block->toBlade($settings, new BladeWriter($this->context), $this->context)->toBlade();
    $text = $this->block->toText($settings, new TextWriter($this->context), $this->context)->toBlade();

    expect($html)->toStartWith(" @partial('table')<table")
        ->and($html)->toContain('<th width="50%" align="left">Name/Description</th>')
        ->and($html)->toContain(' @foreach($order_menus as $order_menu)')
        ->and($html)->toContain("{{ \$order_menu['menu_quantity'] }} x {{ \$order_menu['menu_name'] }}<br>{!! \$order_menu['menu_options'] !!}<br>{{ \$order_menu['menu_comment'] }}")
        ->and($html)->toContain(' @foreach($order_totals as $order_total)')
        ->and($text)->toContain("{{ \$order_menu['menu_quantity'] }} x {{ \$order_menu['menu_name'] }}")
        ->and($text)->toContain("{{ \$order_total['order_total_title'] }}: {{ \$order_total['order_total_value'] }}");

    $this->builder->assertSafe($html);
    $this->builder->assertSafe($text);
});

it('leaves out options, comments and totals when turned off', function(): void {
    $settings = $this->block->sanitize(['show_options' => false, 'show_comments' => false, 'show_totals' => false]);
    $html = $this->block->toBlade($settings, new BladeWriter($this->context), $this->context)->toBlade();
    $text = $this->block->toText($settings, new TextWriter($this->context), $this->context)->toBlade();

    expect($html)->not->toContain('menu_options')
        ->and($html)->not->toContain('menu_comment')
        ->and($html)->not->toContain('order_totals')
        ->and($text)->not->toContain('order_totals');
});

it('compiles without the table partial and with fewer item fields', function(): void {
    $context = new CompileContext(CompileContext::MODE_TEMPLATE, new MailVariables(['Order' => [
        '$order_menus[]' => 'Menus',
        '{{ $order_menu[\'menu_name\'] }}' => 'Name',
    ]]));

    $html = $this->block->toBlade($this->block->sanitize([]), new BladeWriter($context), $context)->toBlade();

    expect($html)->toStartWith('<table')
        ->and($html)->toContain("<tr><td> x {{ \$order_menu['menu_name'] }}</td><td align=\"right\"></td><td align=\"right\"></td></tr>");
});

it('compiles nothing for templates without order variables', function(): void {
    $context = $this->builder->makeContext(CompileContext::MODE_TEMPLATE, 'igniter.cart::mail.low_stock_alert');

    expect($this->block->toBlade([], new BladeWriter($context), $context)->isEmpty())->toBeTrue()
        ->and($this->block->toText([], new TextWriter($context), $context)->isEmpty())->toBeTrue()
        ->and($this->block->importChunk("@partial('table')@foreach(\$order_menus as \$order_menu)@endforeach@endpartial", $context))->toBeNull();
});

it('imports the order table of the registered order views', function(string $view): void {
    $html = MailParser::parse(File::get(View::make($view)->getPath()))['html'];
    $context = $this->builder->makeContext(CompileContext::MODE_TEMPLATE, $view);

    $result = resolve(MailImporter::class)->import($html, $context, $view);
    $items = collect($result['blocks'])->firstWhere('code', 'igniter.cart::order_items');

    expect(array_column($result['blocks'], 'code'))->not->toContain('legacy')
        ->and($result['notices'])->toBe([])
        ->and($items['settings'])->toMatchArray([
            'label_name' => 'Name/Description',
            'label_price' => 'Unit Price',
            'label_subtotal' => 'Sub Total',
            'show_options' => true,
            'show_comments' => true,
            'show_totals' => true,
        ]);
})->with([
    ['igniter.cart::mail.order'],
    ['igniter.cart::mail.order_alert'],
]);

it('ignores chunks that are not the order table', function(): void {
    expect($this->block->importChunk('Some text', $this->context))->toBeNull()
        ->and($this->block->importChunk("@partial('panel')x@endpartial", $this->context))->toBeNull();
});
