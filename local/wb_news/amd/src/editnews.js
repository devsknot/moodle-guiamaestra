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

/*
 * @package    local_wb_news
 * @copyright  Wunderbyte GmbH <info@wunderbyte.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';

const SELECTORS = {
    ALLINSTANCES: '[data-id="wb-news-all-instances-container"]', // Via shortcodes, we can have more than one of these.
    ADDEDITBUTTON: 'div.wb-news-addeditbutton',
    DELETEBUTTON: 'div.wb-news-deletebutton',
};

export const init = () => {
    // eslint-disable-next-line no-console
    console.log('run init');

    // Cashout functionality.
    const containers = document.querySelectorAll(SELECTORS.ALLINSTANCES);

    // eslint-disable-next-line no-console
    console.log('containers', containers);

    containers.forEach(container => {

        if (container.dataset.initialized) {
            return;
        }

        container.dataset.initialized = 'true';

        container.addEventListener('click', e => {
            handleClickEvent(e);
        });
    });
};


/**
 * Handles the click event on the wb news container.
 *
 * @param {mixed} event
 *
 * @return void
 *
 */
function handleClickEvent(event) {

    // eslint-disable-next-line no-console
    console.log(event.target);

    // Get event target.
    if (event.target && event.target.dataset.action) {

        const action = event.target.dataset.action;

        switch (action) {
            case 'add':
                addeditformModal(event.target);
                break;
            case 'edit':
                addeditformModal(event.target);
                break;
            case 'delete':
                deleteModal(event.target);
                break;
            case 'copy':
                copyModal(event.target);
                break;
            default:
                // eslint-disable-next-line no-console
                console.log('Unknown action:', action);
        }
    }
}

/**
 * Show add edit form.
 * @param {htmlElement} button
 * @return [type]
 *
 */
export function addeditformModal(button) {

    // eslint-disable-next-line no-console
    console.log('button', button);

    const id = button.dataset.id ? button.dataset.id : 0;
    const instanceid = button.dataset.instanceid;
    const isinstance = button.dataset.isinstance;

    let formclass = "local_wb_news\\form\\addeditmodal";
    if (isinstance == 'true') {
        formclass = "local_wb_news\\form\\addeditinstancemodal";
    }

    var title = getString('addform', 'local_wb_news');
    if (id > 0) {
        title = getString('editform', 'local_wb_news');
    }

    const modalForm = new ModalForm({

        // Name of the class where form is defined (must extend \core_form\dynamic_form):
        formClass: formclass,
        // Add as many arguments as you need, they will be passed to the form:
        args: {
            id,
            instanceid
        },
        // Pass any configuration settings to the modal dialogue, for example, the title:
        modalConfig: {title},
        // DOM element that should get the focus after the modal dialogue is closed:
        returnFocus: button
    });
    // Listen to events if you want to execute something on form submit.
    // Event detail will contain everything the process() function returned:
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
        let anchor = `#instance-${instanceid}`;
        location.href = `${location.origin}${location.pathname}${anchor}`;
        location.reload();
    });

    // Show the form.
    modalForm.show();

}

/**
 * Show add edit form.
 * @param {htmlElement} button
 * @return [type]
 *
 */
export function deleteModal(button) {

    // eslint-disable-next-line no-console
    console.log('button', button);

    const id = button.dataset.id;
    const instanceid = button.dataset.instanceid;
    const isinstance = button.dataset.isinstance;

    let formclass = "local_wb_news\\form\\deletemodal";
    if (isinstance == 'true') {
        formclass = "local_wb_news\\form\\deleteinstancemodal";
    }

    const modalForm = new ModalForm({

        // Name of the class where form is defined (must extend \core_form\dynamic_form):
        formClass: formclass,
        // Add as many arguments as you need, they will be passed to the form:
        args: {
            id,
            instanceid
        },
        // Pass any configuration settings to the modal dialogue, for example, the title:
        modalConfig: {title: getString('deletenewsitem', 'local_wb_news')},
        // DOM element that should get the focus after the modal dialogue is closed:
        returnFocus: button
    });
    // Listen to events if you want to execute something on form submit.
    // Event detail will contain everything the process() function returned:
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
        let anchor = `#instance-${instanceid}`;
        location.href = `${location.origin}${location.pathname}${anchor}`;
        location.reload();
    });

    // Show the form.
    modalForm.show();

}

/**
 * Copy form.
 * @param {htmlElement} button
 * @return [type]
 *
 */
export function copyModal(button) {

    // eslint-disable-next-line no-console
    console.log('button', button);

    const id = button.dataset.id;
    const instanceid = button.dataset.instanceid;
    const isinstance = button.dataset.isinstance;

    let formclass = "local_wb_news\\form\\addeditmodal";
    if (isinstance == 'true') {
        formclass = "local_wb_news\\form\\addeditinstancemodal";
    }

    const modalForm = new ModalForm({

        // Name of the class where form is defined (must extend \core_form\dynamic_form):
        formClass: formclass,
        // Add as many arguments as you need, they will be passed to the form:
        args: {
            id,
            instanceid,
            copy: 1
        },
        // Pass any configuration settings to the modal dialogue, for example, the title:
        modalConfig: {title: getString('copyitem', 'local_wb_news')},
        // DOM element that should get the focus after the modal dialogue is closed:
        returnFocus: button
    });
    // Listen to events if you want to execute something on form submit.
    // Event detail will contain everything the process() function returned:
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
        let anchor = `#instance-${instanceid}`;
        location.href = `${location.origin}${location.pathname}${anchor}`;
        location.reload();
    });

    // Show the form.
    modalForm.show();

}