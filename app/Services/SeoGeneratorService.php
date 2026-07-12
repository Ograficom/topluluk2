<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SeoGeneratorService
{
    public $settings;

    public function __construct()
    {
        $this->settings = settings()->group('seo')->all($fresh = false);
    }

    public function getMetaRobotsIndexble(): string
    {
        return '<meta name="robots" content="index,follow,max-image-preview:large">';
    }

    public function getMetaRobotsNoindexble(): string
    {
        return '<meta name="robots" content="noindex,nofollow">';
    }

    public function getMetaCsrfToken(): string
    {
        return '<meta name="csrf-token" content="'.csrf_token().'">';
    }

    public function getMetaDescription(): string
    {
        return ! empty($this->settings['meta_description']) ? $this->settings['meta_description'] : '';
    }

    public function getCurrentCanonicalUrl($route): string
    {
        return '<link rel="canonical" href="'.$route.'">';
    }

    public function getOGDescription(): string
    {
        return ! empty($this->settings['og_description']) ? $this->settings['og_description'] : '';
    }

    public function getMetaImage(): string
    {
        return ! empty($this->settings['og_image']) ? Storage::disk(getCurrentDisk())->url($this->settings['og_image']) : '';
    }

    public function getOGSiteName(): string
    {
        return '<meta property="og:site_name" content="'.$this->settings['og_site_name'].'">';
    }

    public function getOGType(): string
    {
        return '<meta property="og:type" content="'.$this->settings['og_type'].'">';
    }

    public function getOGLocale(): string
    {
        return '<meta property="og:locale" content="'.app()->getLocale().'">';
    }

    public function getOGUrl(): string
    {
        return '<meta property="og:url" content="'.url()->current().'">';
    }
}
