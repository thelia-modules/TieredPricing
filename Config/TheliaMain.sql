
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- product_tier_discount
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `product_tier_discount`;

CREATE TABLE `product_tier_discount`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER,
    `product_sale_elements_id` INTEGER,
    `tier_quantity` INTEGER NOT NULL,
    `discount_percent` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fi_product_tier_discount_product_id` (`product_id`),
    INDEX `fi_product_tier_discount_product_sale_elements_id` (`product_sale_elements_id`),
    CONSTRAINT `fk_product_tier_discount_product_id`
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_product_tier_discount_product_sale_elements_id`
        FOREIGN KEY (`product_sale_elements_id`)
        REFERENCES `product_sale_elements` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
