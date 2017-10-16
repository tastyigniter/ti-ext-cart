<?php namespace SamPoyigi\Cart\Models;

use Model;

class Settings_model extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'sampoyigi.cart';

    // Reference to field configuration
    public $settingsFieldsConfig = 'settings_model';

    protected function validateForm()
    {
        $this->form_validation->set_rules('show_cart_images', 'lang:label_show_cart_images', 'required|integer');
        $this->form_validation->set_rules('fixed_cart', 'lang:label_fixed_cart', 'required|integer');

        if ($this->input->post('fixed_cart') == '1') {
            $this->form_validation->set_rules('fixed_top_offset', 'lang:label_fixed_top_offset', 'required|integer');
            $this->form_validation->set_rules('fixed_bottom_offset', 'lang:label_fixed_bottom_offset', 'required|integer');
        }

        if ($this->input->post('show_cart_images') == '1') {
            $this->form_validation->set_rules('cart_images_h', 'lang:label_cart_images_h', 'required|integer');
            $this->form_validation->set_rules('cart_images_w', 'lang:label_cart_images_w', 'required|integer');
        }

        if ($this->input->post('totals')) {
            foreach ($this->input->post('totals') as $key => $value) {
                $this->form_validation->set_rules('totals['.$key.'][title]', "[{$key}] ".lang('column_title'), 'required|max:128');
                $this->form_validation->set_rules('totals['.$key.'][admin_title]', "[{$key}] ".lang('column_admin_title'), 'required|max:128');
                $this->form_validation->set_rules('totals['.$key.'][name]', "[{$key}] ".lang('column_name'), 'required|alpha_dash');
                $this->form_validation->set_rules('totals['.$key.'][status]', "[{$key}] ".lang('column_display'), 'required|integer');
            }
        }

        if ($this->form_validation->run() === TRUE) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
}
