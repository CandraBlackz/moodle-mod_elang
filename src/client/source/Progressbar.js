/**
 * Progressbar kind
 *
 * @package     mod_elang
 *
 * @copyright   2013-2018 University of La Rochelle, France
 * @license     http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html CeCILL-B license
 *
 * @since       0.0.1
 */
enyo.kind(
	{
		/**
		 * Name of the kind
		 */
		name: "Elang.Progressbar",

		/**
		 * Published properties:
		 * - begin: the minimum value
		 * - end: the maximum value
		 * - success: the success cursor between the minimum and the maximum (rendered in green)
		 * - info: the info cursor between the minimum and the maximum (rendered in blue)
		 * - warning: the warning cursor between the minimum and the maximum (rendered in orange)
		 * - danger: the danger cursor between the minimum and the maximum (rendered in red)
		 * Each property will have public setter and getter methods
		 */
		published: {begin: 0, end: 0, success: 0, info: 0, warning: 0, danger: 0},

		/**
		 * css classes
		 */
		classes: 'progress',

		/**
		 * Named components:
		 * - success
		 * - info
		 * - warning
		 * - danger
		 */
		components:  [
			{name: 'success', classes: 'progress-bar progress-bar-success', style: 'width: 0%;', attributes: {title: $L('studentsuccess'), 'data-toggle':'tooltip'}},
			{name: 'info', classes: 'progress-bar progress-bar-info', style: 'width: 0%;', attributes: {title: $L('studenthelp'), 'data-toggle':'tooltip'}},
			{name: 'warning', classes: 'progress-bar progress-bar-warning', style: 'width: 0%;'},
			{name: 'danger', classes: 'progress-bar progress-bar-danger', style: 'width: 0%;', attributes: {title: $L('studenterror'), 'data-toggle':'tooltip'}}
		],

		/**
		 * Detect a change in the begin property
		 *
		 * @protected
		 *
		 * @param   oldValue  integer  The begin old value
		 *
		 * @return  void
		 *
		 * @since  0.0.1
		 */
		beginChanged: function (oldValue)
		{
			this.setData('success', this.success);
			this.setData('info', this.info);
			this.setData('warning', this.warning);
			this.setData('danger', this.danger);
		},

		/**
		 * Detect a change in the end property
		 *
		 * @protected
		 *
		 * @param   oldValue  integer  The end old value
		 *
		 * @return  void
		 *
		 * @since  0.0.1
		 */
		endChanged: function (oldValue)
		{
			this.setData('success', this.success);
			this.setData('info', this.info);
			this.setData('warning', this.warning);
			this.setData('danger', this.danger);
		},

		/**
		 * Detect a change in the success property
		 *
		 * @protected
		 *
		 * @param   oldValue  integer  The success old value
		 *
		 * @return  void
		 *
		 * @since  0.0.1
		 */
		successChanged: function (oldValue)
		{
			this.setData('success', this.success);
		},

		/**
		 * Detect a change in the info property
		 *
		 * @protected
		 *
		 * @param   oldValue  integer  The info old value
		 *
		 * @return  void
		 *
		 * @since  0.0.1
		 */
		infoChanged: function (oldValue)
		{
			this.setData('info', this.info);
		},

		/**
		 * Detect a change in the warning property
		 *
		 * @protected
		 *
		 * @param   oldValue  integer  The warning old value
		 *
		 * @return  void
		 *
		 * @since  0.0.1
		 */
		warningChanged: function (oldValue)
		{
			this.setData('warning', this.warning);
		},

		/**
		 * Detect a change in the danger property
		 *
		 * @protected
		 *
		 * @param   oldValue  integer  The danger old value
		 *
		 * @return  void
		 *
		 * @since  0.0.1
		 */
		dangerChanged: function (oldValue)
		{
			this.setData('danger', this.danger);
		},

		/**
		 * Change the a component data
		 *
		 * @protected
		 *
		 * @param   component  string   The component name
		 * @param   newValue   integer  The new data value
		 *
		 * @return  this
		 *
		 * @since  0.0.1
		 */
		setData: function (component, newValue)
		{
			var percent, numerator = newValue - this.begin, denominator = this.end - this.begin;

			if (numerator < 0 || denominator == 0)
			{
				percent = 0;
			}
			else if (numerator > denominator)
			{
				percent = 100;
			}
			else
			{
				percent = (numerator / denominator) * 100;
			}

			this.$[component].applyStyle('width', percent + '%');
			return this;
		},
	}
);
