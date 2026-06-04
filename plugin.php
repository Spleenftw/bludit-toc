<?php
/**
 * Bludit ToC Plugin
 *
 * Injects a sticky Table of Contents sidebar (desktop) + floating drawer
 * (mobile) built from the article's h2/h3/h4 headings. Works standalone
 * on any Bludit theme that wraps article body in a `.content` element.
 *
 * Drop this directory into bl-plugins/ and activate via the admin panel.
 */
class PluginBluditToc extends Plugin {

    public function init() {
        $this->dbFields = array(
            // Label shown at the top of the sidebar and in the mobile drawer.
            'title'        => 'On this page',
            // Pixel offset to clear the fixed navbar when jumping to a heading.
            'navbarHeight' => 80,
            // Viewport width (px) at which the fixed sidebar appears.
            // Below this width the mobile floating-button drawer is used instead.
            'minWidth'     => 1280,
        );
    }

    // ---- Admin settings form ----

    public function form() {
        global $L;

        $title        = htmlspecialchars($this->getDbField('title'), ENT_QUOTES, 'UTF-8');
        $navbarHeight = (int) $this->getDbField('navbarHeight');
        $minWidth     = (int) $this->getDbField('minWidth');

        $h  = '';

        $h .= '<div class="form-group">';
        $h .= '<label for="bltoc-field-title">' . $L->get('Sidebar title') . '</label>';
        $h .= '<input class="form-control" type="text" id="bltoc-field-title" name="title" value="' . $title . '">';
        $h .= '</div>';

        $h .= '<div class="form-group">';
        $h .= '<label for="bltoc-field-navbar">' . $L->get('Navbar height (px)') . '</label>';
        $h .= '<input class="form-control" type="number" id="bltoc-field-navbar" name="navbarHeight" min="0" max="300" value="' . $navbarHeight . '">';
        $h .= '<small class="form-text text-muted">' . $L->get('Used to offset hash-jump anchors so they clear the fixed navbar.') . '</small>';
        $h .= '</div>';

        $h .= '<div class="form-group">';
        $h .= '<label for="bltoc-field-minwidth">' . $L->get('Fixed sidebar min-width (px)') . '</label>';
        $h .= '<input class="form-control" type="number" id="bltoc-field-minwidth" name="minWidth" min="768" max="2560" value="' . $minWidth . '">';
        $h .= '<small class="form-text text-muted">' . $L->get('Viewport widths below this value use the mobile floating button instead.') . '</small>';
        $h .= '</div>';

        return $h;
    }

    // ---- Front-end hooks ----

    /**
     * Inject the plugin CSS in <head>.
     */
    public function siteHead() {
        $url = DOMAIN_PLUGINS . $this->directoryName . '/toc.css';
        return HTML::style($url) . PHP_EOL;
    }

    /**
     * Inject the ToC sidebar container right after <body>.
     * It is position:fixed, so placement in the DOM doesn't affect layout.
     * JS will populate <nav id="bltoc-nav"> and mark the container visible.
     */
    public function siteBodyBegin() {
        $title = htmlspecialchars($this->getDbField('title'), ENT_QUOTES, 'UTF-8');
        return '<aside class="bltoc-sidebar" id="bltoc-sidebar" aria-label="' . $title . '">'
             . '<div class="bltoc-header"><span class="bltoc-title">' . $title . '</span></div>'
             . '<nav id="bltoc-nav" aria-label="' . $title . '"></nav>'
             . '</aside>' . PHP_EOL;
    }

    /**
     * Inject config + script just before </body>.
     * Config is passed via window.BLTOC so the JS file stays cache-friendly.
     */
    public function siteBodyEnd() {
        $title        = json_encode($this->getDbField('title'));
        $navbarHeight = (int) $this->getDbField('navbarHeight');
        $minWidth     = (int) $this->getDbField('minWidth');
        $url          = DOMAIN_PLUGINS . $this->directoryName . '/toc.js';

        $out  = '<script>window.BLTOC={title:' . $title . ',navbarHeight:' . $navbarHeight . ',minWidth:' . $minWidth . '};</script>' . PHP_EOL;
        $out .= HTML::script($url) . PHP_EOL;
        return $out;
    }
}
