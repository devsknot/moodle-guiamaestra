<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the booking module.
 *
 * @package local_wb_news
 * @copyright 2024 Wunderbyte GmbH <info@wunderbyte.at>
 * @author Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_wb_news\output;

use plugin_renderer_base;
use templatable;

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the booking module.
 *
 * @package local_wb_news
 * @copyright 2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author David Bogner, Andraž Prinčič
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Render wbnews
     *
     * @param mixed $data
     *
     * @return [type]
     *
     */
    public function render_newsview($data) {
        $data = $data->export_for_template($this);
        return $this->render_from_template('local_wb_news/wb_news_blog_detail_view', $data);
    }

    /**
     * Render wbnews instance
     *
     * @param mixed $data
     *
     * @return [type]
     *
     */
    public function render_newsinstance($data) {
        $data = $data->export_for_template($this);
        return $this->render_from_template('local_wb_news/wb_news_container', $data);
    }
}
