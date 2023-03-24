@extends('layouts.app')
@section('title','Purchase Retailer IDs')
@section('customcss')
<style>
.table thead th {
	background: transparent !important;
}
input[type="button"] {
  -webkit-appearance: button;
  cursor: pointer;
  
}
.input-group input[type='button'] {
  background-color: #eeeeee;
  min-width: 38px;
  width: auto;
  transition: all 300ms ease;
  border: none;
}

.input-group .button-minus,
.input-group .button-plus {
  font-weight: bold;
  height: 38px;
  padding: 0;
  width: 38px;
  position: relative;
}

.ish-tbl td {
    line-height: 38px;
}
</style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="card shadow mb-3" id="ub-card">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold">
      <i class="fas fa-user-plus pr-2"></i> Purchase Retailer IDs</h6>
    </div>
    <div class="card-body">
      
      <div class="row">
        
        <div class="col-md-12">
          <table class="table table-responsive ish-tbl" >
            <thead>
              <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <form class="form" name="checkout_form" id="checkout_form" action="{{ route('post:purchase_retailer_ids')}}" method="post">
                  @csrf
                  <input type="hidden" value="{{ env('RETAILER_IDS_FEE') }}" name="product_price" id="product_price">
                  <input type="hidden" value="{{ env('RETAILER_IDS_FEE') * env('RETAILER_IDS_MIN_QTY') }}" name="final_amount" id="final_amount">
                  
                  <td>Retailer IDs</td>
                  <td><small><i class="fas fa-fw fa-rupee-sign"></i></small><strong>{{ env('RETAILER_IDS_FEE') }}</strong></td>
                  <td class="input-group">
                    <input type="button" value="-" class="button-minus" data-field="quantity">
                    <input type="number" value="10" min="10" id="quantity" name="quantity" class="form-control" style="width:125px;">
                    <input type="button" value="+" class="button-plus" data-field="quantity"></td>
                  <td><small><i class="fas fa-fw fa-rupee-sign"></i></small><strong><span id="payable_amount">{{ env('RETAILER_IDS_FEE') * env('RETAILER_IDS_MIN_QTY') }}</span></strong></td>
                  <td><button class="btn btn-primary btn-small">Submit</button></td>
                </form>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('customjs')
<script>

function incrementValue(e) {
  e.preventDefault();
  var fieldName = 'quantity';
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal)) {
    parent.find('input[name=' + fieldName + ']').val(currentVal + 1);
    var product_price = $("#product_price").val();
    pricecount(currentVal + 1,product_price);
  } else {
    parent.find('input[name=' + fieldName + ']').val(0);
    var product_price = $("#product_price").val();
    pricecount(0,product_price);
  }
}

function decrementValue(e) {
  e.preventDefault();
  var fieldName = 'quantity';
  var parent = $(e.target).closest('div');
  var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);

  if (!isNaN(currentVal) && currentVal > 0) {
    parent.find('input[name=' + fieldName + ']').val(currentVal - 1);
    var product_price = $("#product_price").val();
    pricecount(currentVal - 1,product_price);
  } else {
    parent.find('input[name=' + fieldName + ']').val(0);
    var product_price = $("#product_price").val();
    pricecount(0,product_price);
  }
  
}
$('.input-group').on('click', '.button-plus', function(e) {
  incrementValue(e);
});

$('.input-group').on('click', '.button-minus', function(e) {
  decrementValue(e);
});

$(document).ready(function() {
    $("#quantity").val({{ env('RETAILER_IDS_MIN_QTY') }});
});

$("#quantity").change(function () {
    var quantity = this.value;
    var product_price = $("#product_price").val();
    var payable_amount = quantity*product_price;
    
    $("#payable_amount").html(payable_amount);
    $("#final_amount").val(payable_amount);
    $("#order_quantity").val(quantity);
});

$("[type='number']").keypress(function (evt) {
    evt.preventDefault();
});

function pricecount(quantity,product_price){
    var payable_amount = quantity*product_price;
    
    $("#payable_amount").html(payable_amount);
    $("#final_amount").val(payable_amount);
    $("#order_quantity").val(quantity);
}
</script>
@endsection