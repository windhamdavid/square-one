/**
 * @module
 * @description JavaScript specific to the panel spacing utility.
 */

import * as tools from 'utils/tools';
import {PANEL_SPACER_VARS} from '../config/wp-settings';
import prettyData from 'pretty-data';

const el = {
    container: tools.getNodes('panel-spacing-wrapper')[0],
    codeSnippet: tools.getNodes('panel-spacing-output')[0],
    spacers: tools.getNodes('panel-spacer', true),
};

const updateCSS = () => {
    const rules = [];
    el.spacers.forEach(spacer => {
        const attributes = spacer.dataset;
        const parsedBefore = attributes.prevClass.replace(/ /g, '.');
        const parsedAfter = attributes.nextClass.replace(/ /g, '.');
        const currentMargin = attributes.nextMargin;
        let definedSpacer = attributes.definedSpacer;

        if ( 0 == definedSpacer || 'undefined' === typeof definedSpacer || ! definedSpacer.length ) {
            return;
        }

        if (definedSpacer.indexOf('--spacer') !== -1) {
            definedSpacer = `var(${definedSpacer})`;
        } else {
            definedSpacer = `${definedSpacer}px`;
        }

        const selector = `.${parsedBefore} + .${parsedAfter}`;
        const newMargin = '0px' === currentMargin ? definedSpacer : `calc(${definedSpacer} + ${currentMargin})`;
        const fullRule = `${selector} { margin-top: ${newMargin}; }`;

        rules.push(fullRule);
    });

    let code = rules.join(' ');
    code = prettyData.pd.css(code);

    el.codeSnippet.innerHTML = PR.prettyPrintOne(code, 'css');
};

const showSpacerControls = (e) => {
    const target = e.target;
    const parent = tools.closest(target, '.panel-spacer');

    parent.classList.toggle('active');

    el.spacers.forEach(item => {
        if (item === parent) {
            return;
        }
        item.classList.remove('active');
    });
};

const getSpacerVarValue = (value) => {
    if ( 'undefined' === typeof PANEL_SPACER_VARS[value]) {
        return value;
    }

    return PANEL_SPACER_VARS[value].replace('px', '');
};

const updateSpacer = (e) => {
    const target = e.target;
    const parent = tools.closest(target, '.panel-spacer');
    let value = e.target.value;

    if ( ! value.length ) {
       value = 0;
    }

    parent.setAttribute('data-defined-spacer', value);

    value = getSpacerVarValue(value);

    parent.style.height = value + 'px';

    updateCSS();
};

/**
 * @function bindEvents
 * @description Bind the events for this module.
 */

const bindEvents = () => {
    el.spacers.forEach(item => {
        const icons = tools.getNodes('.panel-spacer__icon', true, item, true);

        icons.forEach(icon => {
            icon.addEventListener('click', showSpacerControls);
        });

        const children = tools.getChildren(item);
        children.forEach(child => {
            child.addEventListener('change', updateSpacer);
        });
    });
};

const setupSpacers = () => {
    el.spacers.forEach(item => {
        let prev = item.previousElementSibling;
        let next = item.nextElementSibling;

        item.setAttribute('data-prev-class', prev.className);
        item.setAttribute('data-next-class', next.className);

        item.setAttribute('data-next-margin', window.getComputedStyle(next, null).getPropertyValue('margin-top'));
    });
};

/**
 * @function init
 * @description Kick off this modules functions
 */

const panelSpacing = () => {
    if (!el.container) {
        return;
    }

    bindEvents();

    setupSpacers();

    console.info('Square One FE: Initialized global panel spacing scripts.');
};

export default panelSpacing;
