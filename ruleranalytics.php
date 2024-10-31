<?php
/*
Plugin Name: Ruler Analytics
Plugin URI: http://www.ruleranalytics.com/
Description: Ruler Analytics tracking for your Wordpress site.
Author: Mark Higham
Version: 0.6
Author URI: http://www.ruleranalytics.com
*/

if (!defined('WP_CONTENT_URL'))
    define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH.'wp-content');
if (!defined('WP_PLUGIN_URL'))
    define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
if (!defined('WP_PLUGIN_DIR'))
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

function activate_ruleranalytics() {
    add_option('ruler_site_id', '');
}

function deactive_ruleranalytics() {
    delete_option('ruler_site_id');
}

function admin_init_ruleranalytics() {
    register_setting('ruleranalytics_options', 'ruler_site_id');
}

function admin_menu_ruleranalytics() {
    add_options_page('Ruler Analytics', 'Ruler Analytics', 'manage_options', 'ruleranalytics', 'options_page_ruleranalytics');
}

function options_page_ruleranalytics() {
    include(WP_PLUGIN_DIR.'/ruler-analytics/options.php');
}

function ruleranalytics() {
    $site_id = get_option('ruler_site_id');

    if($site_id == ''){
        echo "<!-- Ruler Analytics Code here -->";
        return;
    }

    $snippet = <<<SNIPPET

    <script type="text/javascript">

        var __raconfig = __raconfig || {};

        __raconfig.uid = '$site_id';
        __raconfig.action = 'track';

        (function () {
            var ra = document.createElement('script');
            ra.type = 'text/javascript';
            ra.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'www.ruleranalytics.com/lib/1.0/ra-bootstrap.min.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ra, s);
        }());

    </script>

SNIPPET;

    echo $snippet;
}

register_activation_hook(__FILE__, 'activate_ruleranalytics');
register_deactivation_hook(__FILE__, 'deactive_ruleranalytics');

if (is_admin()) {
    add_action('admin_init', 'admin_init_ruleranalytics');
    add_action('admin_menu', 'admin_menu_ruleranalytics');
}

if (!is_admin()) {
    add_action('wp_footer', 'ruleranalytics');
    add_action("gform_after_submission", "ruleranalytics_set_post_content", 10, 2);

}


function ruleranalytics_set_post_content($entry, $form)
{

    class RulerGravityFormsConversion
    {
        function __construct($uid, $entry, $form)
        {
            $this->payload = array();
            $this->uid = $uid;
            $this->action = 'convert';
            $this->referrer = $_SERVER['HTTP_REFERER'];
            $this->url = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            $this->remote_addr = (isset($serverVariables['REMOTE_ADDR'])) ? $serverVariables['REMOTE_ADDR'] : null;
            $this->userAgent = (isset($serverVariables['HTTP_USER_AGENT']) ? $serverVariables['HTTP_USER_AGENT'] : null);
            $this->visit_id = isset($_COOKIE['__rasesh']) ? $_COOKIE['__rasesh'] : 'NULVID';
            for ($i = 0; $i < count($form["fields"]); $i++) {
                $field = $form["fields"][$i];
                $type = $field['type'];
                if (null == $field["inputs"]) {
                    $fieldValue = $entry[$field["id"]];
                    $this->AddPayload($type, $fieldValue);
                } else {
                    for ($j = 0; $j < count($field['inputs']); $j++) {
                        $id = (string)$field['inputs'][$j]['id'];
                        $fieldName = $type . '_' . $field['inputs'][$j]['label'];
                        $this->AddPayload($fieldName, $entry[$id]);
                    }

                }
            }

        }

        private function AddPayload($name, $value)
        {
            $this->payload[] = array($name, $value);
        }

        public function GetPostUrl()
        {
            $url = "http://www.ruleranalytics.com/lib/1.0/ra-tracker.js.php";
            $url .= '?ref=' . urlencode($this->referrer) .
                '&href=' . urlencode($this->url) .
                '&visitid=' . urlencode($this->visit_id) .
                '&action=' . urlencode($this->action) .
                '&uid=' . urlencode($this->uid) . '&';

            $payload = array();
            for ($i = 0; $i < count($this->payload); $i++) {

                $payload[] = $this->payload[$i][0] . '=' . urlencode($this->payload[$i][1]);
            }
            $payloadvals = implode('&', $payload);
            $url .= $payloadvals;
            return $url;
        }
    }

    $uid = get_option("ruler_site_id");
    $rulerpost = new RulerGravityFormsConversion($uid, $entry, $form);
    $post_url = $rulerpost->GetPostUrl();

    $request = new WP_Http();
    $response = $request->get($post_url);

}