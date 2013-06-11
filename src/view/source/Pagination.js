/**
 * Pagination kind
 *
 * @package     mod
 * @subpackage  elang
 * @copyright   2013 University of La Rochelle, France
 * @license     http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html CeCILL-B license
 */
enyo.kind({
	/**
	 * Name of the kind
	 */
	name: 'Elang.Pagination',

	/**
	 * css classes
	 */
	classes: 'pagination pagination-centered',

	/**
	 * Published properties:
	 * - total: total number of pages
	 * Each property will have a public setter and a getter method
	 */
	published: {total: 0},

	/**
	 * Named components
	 */
	components: [{name: 'pages', tag: 'ul'}],

	/**
	 * Events:
	 * - onCueTap: fired when a page is tapped
	 */
	events: {onPageChange: ''},

	/**
	 * Detect a change in the total number of pages
	 *
	 * @protected
	 *
	 * @param  oldValue  integer  Old total number of pages
	 *
	 * @return  void
	 */
	totalChanged: function (oldValue)
	{
		this.$.pages.destroyClientControls();
		if (this.total > 1)
		{
			for (var i=0; i < this.total; i++)
			{
				this.$.pages.createComponent(
					{
						tag: 'li',
						number: i,
						classes: i == 0 ? 'active' : '',
						components: [{tag: 'a', ontap: 'pageTap', attributes: {href: '#'}, content: i + 1}]
					},
					{owner: this}
				);
			}
			this.$.pages.show();
		}
		else if (this.total == 0)
		{
			this.$.pages.hide();
		}
		else
		{
			var total = this.total;
			this.total = oldValue;
			throw new RangeError('Total value "' + total + '" is incorrect');
		}
	},

	/**
	 * Handle tap event on a page number
	 *
	 * @protected
	 *
	 * @param  inSender  enyo.instance  Sender of the event
	 * @param  inEvent   Object		 Event fired
	 *
	 * @return void
	 */
	pageTap: function(inSender, inEvent)
	{
		enyo.forEach(
			this.$.pages.getClientControls(),
			function (page) {
				if (page.hasClass('active'))
				{
					if (page != inSender.container)
					{
						page.removeClass('active');
						this.doPageChange({number: inSender.container.number});
					}
				}
			},
			this
		);
		inSender.container.addClass('active');
	},
});
