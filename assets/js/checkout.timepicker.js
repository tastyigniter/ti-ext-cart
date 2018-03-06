+function ($) {
    "use strict"

    if ($.fn === undefined) $.fn = {}

    if ($.fn.checkoutTimePicker === undefined)
        $.fn.checkoutTimePicker = {}

    var CheckoutTimePicker = function (element, options) {
        this.$el = $(element)
        this.$dateElement = this.$el.find('[data-timepicker="date"]')
        this.$hourElement = this.$el.find('[data-timepicker="hour"]')
        this.$minuteElement = this.$el.find('[data-timepicker="minute"]')

        this.options = options

        this.init()
        this.fillDates()
    }

    CheckoutTimePicker.prototype.constructor = CheckoutTimePicker

    CheckoutTimePicker.prototype.dispose = function () {
        this.unregisterHandlers()

        this.$el = null
        this.$widget = null
        this.selectedDate = null
    }

    CheckoutTimePicker.prototype.init = function () {
        this.$el.on('change', '[data-timepicker]', $.proxy(this.onControlChange, this))

        this.registerHandlers()
    }

    CheckoutTimePicker.prototype.registerHandlers = function () {
    }

    CheckoutTimePicker.prototype.unregisterHandlers = function () {

    }

    CheckoutTimePicker.prototype.fillDates = function() {
        var dateOptions = [],
            selectedDate = this.$dateElement.data('timepicker-selected');

        for (var date in this.options.dateRange) {
            var optionEl = '<option value="' + date + '"' + (
                selectedDate === date ? 'selected="selected"' : ''
            ) + '>' + date + '</option>'

            dateOptions.push(optionEl)
        }

        this.$dateElement.html(dateOptions.join("\n"))
        this.$dateElement.change();
    }

    CheckoutTimePicker.prototype.fillHours = function() {
        var selectedDate = this.$dateElement.data('timepicker-selected'),
            selectedHour = this.$hourElement.data('timepicker-selected');

        if (!this.options.dateRange.hasOwnProperty(selectedDate))
            return

        var hourOptions = [];
        for (var hour in this.options.dateRange[selectedDate]) {
            var optionEl = '<option value="' + hour + '"' + (
                selectedHour === hour ? 'selected="selected"' : ''
            ) + '>' + hour + '</option>'

            hourOptions.push(optionEl)
        }

        this.$hourElement.html(hourOptions.join("\n"))
        this.$hourElement.change();
    }

    CheckoutTimePicker.prototype.fillMinutes = function() {
        var selectedDate = this.$dateElement.data('timepicker-selected'),
            selectedHour = this.$hourElement.data('timepicker-selected'),
            selectedMinute = this.$minuteElement.data('timepicker-selected');

        if (!this.options.dateRange.hasOwnProperty(selectedDate))
            return

        if (!this.options.dateRange[selectedDate].hasOwnProperty(selectedHour))
            return

        var minutes = this.options.dateRange[selectedDate][selectedHour]

        var minuteOptions = [];
        for (var minute in minutes) {
            if (minutes.hasOwnProperty(minute)) {
                var optionEl = '<option value="' + minutes[minute] + '"' + (
                    selectedMinute == minutes[minute] ? 'selected="selected"' : ''
                ) + '>' + minutes[minute] + '</option>'

                minuteOptions.push(optionEl)
            }
        }

        this.$minuteElement.html(minuteOptions.join("\n"))
        this.$minuteElement.change();
    }

    // EVENT HANDLERS
    // ============================

    CheckoutTimePicker.prototype.onControlChange = function (event) {
        var $el = $(event.currentTarget),
            picker = $el.data('timepicker')

        switch (picker) {
            case 'date':
                this.$dateElement.data('timepicker-selected', this.$dateElement.val())
                this.fillHours($el)
                break
            case 'hour':
                this.$hourElement.data('timepicker-selected', this.$hourElement.val())
                this.fillMinutes($el)
                break
            case 'minute':
                this.$minuteElement.data('timepicker-selected', this.$minuteElement.val())
                break
        }
    }

    CheckoutTimePicker.DEFAULTS = {
        dateRange: {},
        dateFormat: 'd-m-Y',
        hourFormat: 'H',
    }

    var old = $.fn.checkoutTimePicker

    $.fn.checkoutTimePicker = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result = undefined

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.checkoutTimePicker')
            var options = $.extend({}, CheckoutTimePicker.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.checkoutTimePicker', (data = new CheckoutTimePicker(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.checkoutTimePicker.Constructor = CheckoutTimePicker

    // CART ITEM NO CONFLICT
    // =================

    $.fn.checkoutTimePicker.noConflict = function () {
        $.fn.checkoutTimePicker = old
        return this
    }

    $(document).ready(function () {
        $('[data-control="checkout-timepicker"]').checkoutTimePicker()
    })
}(window.jQuery)
