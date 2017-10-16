+function ($) {
    "use strict"

    var CartBox = function (element, options) {
        this.$el = $(element)
        // this.$form = this.$el.closest('form')
        // this.$mapView = this.$el.find('[data-control="map-view"]')
        // this.mapRefreshed = false
        this.options = options || {}

        this.init()
    }

    CartBox.prototype.init = function () {
        // $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', $.proxy(this.refreshMap, this))
        // $(document).on('hide.bs.collapse', this.$el.find('.collapse'), $.proxy(this.onPanelHidden, this))
        // $(document).on('show.bs.collapse', this.$el.find('.collapse'), $.proxy(this.onPanelShown, this))
        //
        $(document).on('click', '[data-cart-control]', $.proxy(this.onControlClick, this))
        this.$el.on('change', '[data-cart-toggle="order-type"]', $.proxy(this.onOrderTypeToggle, this))
        //
        // $(document).on('click', '[data-control="add-cart-item"]', $.proxy(this.onAddItem, this))
        // $(document).on('click', '[data-control="add-cart-item"]', $.proxy(this.onAddItem, this))
        //
        // this.$el.on('click', '[data-control="update-cart-item"]', $.proxy(this.onUpdateItem, this))
        // this.$el.on('click', '[data-control="remove-cart-item"]', $.proxy(this.onRemoveItem, this))
        // this.$el.on('click', '[data-control="apply-coupon"]', $.proxy(this.applyCoupon, this))
        // this.$el.on('click', '[data-control="add-row"]', this.addRow)
        // this.$el.on('click', '[data-control="remove-panel"]', $.proxy(this.removePanel, this))
        //
        // this.$mapView.on('click.shape.ti.mapview', $.proxy(this.onShapeClicked, this))
        //
        // this.$form.on('submit', $.proxy(this.onSubmitForm, this));
    }

    CartBox.prototype.refreshCart = function (event) {
        console.log('refreshCart')
    }

    CartBox.prototype.loadItem = function () {
        console.log('loadItem')
    }

    CartBox.prototype.addItem = function () {
        console.log('addItem')
    }

    CartBox.prototype.updateItem = function () {
        console.log('updateItem')
    }

    CartBox.prototype.removeItem = function (element) {
        console.log('removeItem')
    }

    // EVENT HANDLERS
    // ============================

    CartBox.prototype.onControlClick = function (event) {
        var control = $(event.currentTarget).data('cart-control')

        switch (control) {
            case 'load-item':
                this.loadItem()
                break
            case 'add-item':
                this.addItem()
                break
            case 'refresh':
                this.refresh()
                break
            case 'update-item':
                this.updateItem()
                break
            case 'remove-item':
                this.removeItem()
                break
            case 'apply-coupon':
                this.applyCoupon()
                break
        }

        return false
    }

    CartBox.prototype.onModalShown = function (event) {
        console.log('onModalShown')
    }

    CartBox.prototype.onModalHidden = function (event) {
        console.log('onModalHidden')
    }

    CartBox.prototype.onOrderTypeToggle = function (event) {
        console.log('onOrderTypeToggle')
    }

    CartBox.prototype.onSubmitForm = function (event) {
        try {
            var shapeData = this.$el.find('[data-control="map-view"]').mapView('getShapeData')
        } catch (ex) {
            throw new Error(ex)
        }

        for (var shapeId in shapeData) {
            var circle = shapeData[shapeId].circle,
                polygon = shapeData[shapeId].polygon,
                vertices = shapeData[shapeId].vertices

            this.$el.find('#' + shapeId + ' [data-shape-value="circle"]').val(JSON.stringify(circle))
            this.$el.find('#' + shapeId + ' [data-shape-value="polygon"]').val(polygon)
            this.$el.find('#' + shapeId + ' [data-shape-value="vertices"]').val(JSON.stringify(vertices))
        }
    }

    CartBox.prototype.onShapeClicked = function (event, mapObject, shape) {
        if (!shape)
            return

        this.$el.find('.collapse').collapse('hide')
        $('#' + shape.getId()).find('.collapse').collapse('toggle')
    }

    CartBox.DEFAULTS = {
        alias: 'cart',
        // areaColors: [],
        // defaultShape: 'polygon',
        // vertices: null,
        // circle: null
    }

    // PLUGIN DEFINITION
    // ============================

    var old = $.fn.cartBox

    $.fn.cartBox = function (option) {
        var args = arguments

        return this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.cartBox')
            var options = $.extend({}, CartBox.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.cartBox', (data = new CartBox(this, options)))
            if (typeof option == 'string') data[option].apply(data, args)
        })
    }

    $.fn.cartBox.Constructor = CartBox

    $.fn.cartBox.noConflict = function () {
        $.fn.cartBox = old
        return this
    }

    $(document).ready(function () {
        $('[data-control="cart-box"]').cartBox()
    })

}(window.jQuery)
