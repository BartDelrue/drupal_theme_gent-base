'use strict';

module.exports = {
  status: 'alpha',
  collated: true,
  collator: function (markup, item) {
    return `<!-- Start: @${item.handle} -->\n<dt>${item.name}</dt><dd>${markup}</dd>\n<!-- End: @${item.handle} -->\n`;
  },
  context: {
    open: true
  },
  variants: [
    {
      name: 'default',
      context: {
        channels: 1
      }
    },
    {
      name: 'multi-channel',
      context: {
        channels: 2
      }
    },
    {
      name: 'closed',
      context: {
        open: false
      }
    }
  ]
};
