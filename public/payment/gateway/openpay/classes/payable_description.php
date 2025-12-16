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

namespace paygw_openpay;

defined('MOODLE_INTERNAL') || die();

use context_system;
use enrol_cart\local\object\cart;

/**
 * Builds human friendly descriptions for payable items.
 */
class payable_description {

    /**
     * Returns a description suitable for payment processors.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @return string
     */
    public static function describe(string $component, string $paymentarea, int $itemid): string {
        if ($component === 'enrol_cart') {
            $cartdescription = self::describe_cart($itemid);
            if ($cartdescription !== '') {
                return $cartdescription;
            }
        }

        return get_string('defaultdescription', 'paygw_openpay', (object) [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
        ]);
    }

    /**
     * Generates a description for enrol_cart items.
     *
     * @param int $cartid
     * @return string
     */
    protected static function describe_cart(int $cartid): string {
        try {
            $cart = cart::find_one($cartid);
            if (!$cart) {
                return '';
            }

            $items = $cart->get_items();
            if (!$items) {
                return '';
            }

            $titles = [];
            foreach ($items as $item) {
                $course = $item->get_course();
                if ($course && !empty($course->title)) {
                    $titles[] = format_string($course->title, true, ['context' => context_system::instance()]);
                }
                if (count($titles) >= 3) {
                    break;
                }
            }

            if (!$titles) {
                return '';
            }

            $coursecsv = implode(', ', $titles);
            $remaining = count($items) - count($titles);

            if ($remaining > 0) {
                return get_string('cartdescriptionmore', 'paygw_openpay', (object) [
                    'courses' => $coursecsv,
                    'count' => $remaining,
                ]);
            }

            return get_string('cartdescription', 'paygw_openpay', $coursecsv);
        } catch (\Throwable $e) {
            // Ignore failures and fall through to default description.
            return '';
        }
    }
}
