<?php
// class-my-custom-plugin-updater.php
class My_Custom_Plugin_Updater {
    private $update_url = 'https://ic27.conohawing.com/wp_plugin/update-info.json'; // 更新情報を取得するURL
    private $plugin_slug;
    private $version;

    public function __construct($plugin_slug, $version) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        
        add_filter('site_transient_update_plugins', [$this, 'check_for_plugin_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
    }

    public function check_for_plugin_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_info = $this->get_remote_update_info();
        if (!$remote_info) {
            return $transient;
        }

        if (version_compare($this->version, $remote_info->version, '<')) {
            $plugin_data = new stdClass();
            $plugin_data->slug = $this->plugin_slug;
            $plugin_data->new_version = $remote_info->version;
            $plugin_data->package = $remote_info->download_url;
            $transient->response[$this->plugin_slug . '/' . $this->plugin_slug . '.php'] = $plugin_data;
        }

        return $transient;
    }

    public function plugin_info($res, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $res;
        }

        $remote_info = $this->get_remote_update_info();
        if (!$remote_info) {
            return $res;
        }

        $res = new stdClass();
        $res->name = $remote_info->name;
        $res->slug = $this->plugin_slug;
        $res->version = $remote_info->version;
        $res->tested = $remote_info->tested;
        $res->requires = $remote_info->requires;
        $res->download_link = $remote_info->download_url;
        return $res;
    }

    private function get_remote_update_info() {
        $response = wp_remote_get($this->update_url);
        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body);
    }
}

?>