/**
 * @module
 * @description Base vendor module for the modern tribe libs js.
 */

import embeds from './embeds';
import forms from './forms';
import socialShare from './social-share';
import panelSpacing from './panel-spacing';

/**
 * @function init
 * @description Kick off this modules functions
 */

const init = () => {
	embeds();

	forms();

	socialShare();

	panelSpacing();
};

export default init;

