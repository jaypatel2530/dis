<label class="label label--block">Payment Type:*</label>
                            <div class="p-t-15">
                                <label class="radio-container m-r-55">Bank Transfer
                                    <input type="radio" name="payment" onclick="paymentMethodSelect()" value="bank" required>
                                    <span class="checkmark"></span>
                                </label>
                                <label class="radio-container m-r-55 disable">PayPal
                                    <input type="radio" name="payment" value="PayPal" onclick="paymentMethodSelect()" checked="checked" required>
                                    <span class="checkmark"></span>
                                </label>
                            </div>