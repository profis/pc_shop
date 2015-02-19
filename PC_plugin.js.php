<?php
/**
 * @var PC_core $core
 * @var PC_plugins $plugins
 * @var array $cfg
 */
?>
var pc_shop_COMBINATION_ATTRIBUTE_COUNT = <?php echo max(v($cfg['pc_shop']['combination_attribute_count'], 1), 1); ?>;
var pc_shop_PRODUCTS_PER_TREE_PAGE = <?php echo max(v($cfg['pc_shop']['products_per_tree_page'], 100), 10); ?>;