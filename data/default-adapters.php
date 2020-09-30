<?php

/**
 * Default adapters for supported providers.
 *
 * Because array indexing is much more efficient and performant than other methods (e.g.
 * searching all the array for a specific value), and also because the main handler is
 * the provider class name (along with its namespace), the first depth of the array
 * contains key-value pairs, in which, the keys are providers names, and the values are
 * arrays mapping operations into preferred (i.e. default) adapters.
 *
 * "Talk is cheap, show me the code" (TM) (Linus Torvalds). See the array structure to
 * understand the description above.
 *
 * @var array
 */
return [
];
