<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateCustomization extends Model
{
    protected $fillable = [
        'primary_color',
        'skin',
        'theme',
        'semi_dark',
        'content_layout',
        'header_type',
        'menu_collapsed',
        'navbar_type',
        'text_direction',
        'footer_fixed',
        'dropdown_on_hover',
        'layout_type'
    ];

    protected $casts = [
        'semi_dark' => 'boolean',
        'menu_collapsed' => 'boolean',
        'footer_fixed' => 'boolean',
        'dropdown_on_hover' => 'boolean',
    ];

    public static function getSettings()
    {
        return self::first() ?? self::create([
            'primary_color' => '#96dbe7ff',
            'skin' => 0,
            'theme' => 'light',
            'semi_dark' => true,
            'content_layout' => 'wide',
            'header_type' => 'static',
            'menu_collapsed' => false,
            'navbar_type' => 'sticky',
            'text_direction' => 'ltr',
            'footer_fixed' => false,
            'dropdown_on_hover' => false,
            'layout_type' => 'horizontal'
        ]);
    }

    public function toJsConfig()
    {
        return [
            'defaultPrimaryColor' => $this->primary_color,
            'defaultSkin' => $this->skin,
            'defaultTheme' => $this->theme,
            'defaultSemiDark' => $this->semi_dark,
            'defaultContentLayout' => $this->content_layout,
            'defaultHeaderType' => $this->header_type,
            'defaultMenuCollapsed' => $this->menu_collapsed,
            'defaultNavbarType' => $this->navbar_type,
            'defaultTextDir' => $this->text_direction,
            'defaultFooterFixed' => $this->footer_fixed,
            'defaultShowDropdownOnHover' => $this->dropdown_on_hover,
            'layoutType' => $this->layout_type,
        ];
    }
}