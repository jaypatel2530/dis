<label class="label label--block">Membership Duration:*</label>
<div class="p-t-15">
    <label class="radio-container m-r-55">1 Year (£{{ $fee*1 }})
        <input type="radio" name="membershipyear" onclick="getPaymentMethod()" value="1" required>
        <span class="checkmark"></span>
    </label>
    <label class="radio-container m-r-55">3 Years (£{{ $fee*3 }})
        <input type="radio" name="membershipyear" onclick="getPaymentMethod()" value="3" required>
        <span class="checkmark"></span>
    </label>
    <label class="radio-container m-r-55">5 Years (£{{ $fee*5 }})
        <input type="radio" name="membershipyear" onclick="getPaymentMethod()" value="5" required>
        <span class="checkmark"></span>
    </label>
</div>