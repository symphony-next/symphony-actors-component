# Proposal: Actors Component
## The problem

Datasource dependencies work really well in Symphony 2, at least for simpler websites, however for some of the larger websites I've worked on things can quickly become a nightmare. Combine this with relatively inflexible Events system has been the cause of some nasty hacks.


### Filter verbosity

One thing that's fairly common in Symphony land is a global Images section -- all images are kept in one easy to query place and then linked to from multiple other sections. This leads to the situation where every datasource for every section linked to images needs to output a parameter, and that parameter needs to be added to the datasource for fetching images.

That's quite a pain in the arse to maintain, for example, here's a snippet from a recent project:

```php
		public $dsParamFILTERS = array(
			'id' => '-1,
				{$ds-business-cards-index.image},
				{$ds-preferences.logo},
				{$ds-units-hotels-latest.images},
				{$ds-units-restaurants-latest.images},
				{$ds-search.images}',
			'54' => 'yes',
		);
```

Keep in mind that this isn't even the worst offender in the codebase, there's a similar datasource with over _50_ separate parameters in one filter.

Wouldn't it be nice if you could just rely on your data without explicitly having to name every single datasource that contains a list of images?


### Event chaining

If you've ever written a complex process in Symphony, say a shopping cart checkout, you've probably tried to write your process as a series of custom events, for example:

- event.checkout_validate.php
- event.checkout_payment.php
- event.checkout_invoice.php

Each of these events should execute in this exact order, however because of the way events are handled you first try to invoice the customer, then process their payment, then validate their request.

Of course this isn't the end of the world and the workaround is relatively easy to do, simply order your events alphabetically:

- event.checkout_1_validate.php
- event.checkout_2_payment.php
- event.checkout_3_invoice.php


### Ordering flexibility

Here's another thing that I've had to hack around to solve, and never managed to solve neatly or succinctly: What if you want an event to execute _after_ a datasource, combining user input and data from a remote datasource in an entry?

In Symphony 2 you can't, events are always executed first, before datasources. Instead you create a wonderful hack, you use a custom event which actually executes and returns the results of a datasource.