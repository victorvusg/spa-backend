###### FLOW LOGICS:
1. Approve Combo (Calculate Combo Price)
    - Cal Total Price
        $combo->total_price = 
            ($combo->variant->price * $combo->amount) / $combo->variant->service->combo_ratio;
    - Add the expiry date to combo: 3 months from approve date
    - Calculate Sale Commission
        $combo->sale_commission = 
            $combo->total_price * $combo->variant->service->combo_commission;
2. Approve intake
   * Loop all orders of intake
       - If order uses combo:
            + Minus combo
       - If order pays money
            + Calculate price and store to order
   * Calculate Final Price
       - If has discount: $final_price = $total_price - $discount_price, minus $customer->points
       - If has additional_discount_price, discount_note: $final_price = $final_price - $additional_discount_price
       - Else: $final_price = $total_price
   * Add point to customer base on total price of intake (formula will be provided)
   * Update status for intake to "is_valid = 1"
3. Review
   * Note: just review for valid intake and intakes which hasn't been reviewed before
         : Review Form: facility, customer_satisfy, note
         : Review for each order: skill, attitude



###### DEFINE
- Pay 50k VND  -> 1 points
- 50 point -> discount 200k VND
- Money: smallest unit = 1000 VND (price = 1 means 1k VND)
- Commission: integer value corresponding percent (commission = 50  -> 50%)
