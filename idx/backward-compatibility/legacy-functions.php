<?php
namespace {
/*
 * global namespace for legacy start and stop functions from IDX Broker Original
 * These functions were sometimes added directly into themes.
 */
    legacy_idx_tags();
    function legacy_idx_tags()
    {
        if (!function_exists('idx_start')) {
            function idx_start()
            {
                return '<div id="idxStart" style="display: none;"></div>';
            }
            function idx_stop()
            {
                return '<div id="idxStop" style="display: none;"></div>';
            }
        }
    }
}
