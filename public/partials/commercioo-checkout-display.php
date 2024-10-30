<?php
/**
 * Commercioo Checkout Form
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Commercioo
 * @subpackage Commercioo/includes
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="section-content">
      <div class="row">
          <div class="col-md-12 d-flex justify-content-center">
              <div class="">
                  <span 
                    class="btn list-arrow-step done"
                    data-bs-container="body"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover"
                    data-bs-placement="top"
                    data-bs-content="The list of products on your shopping cart."
                    >
                    SHOPPING CART
                  </span>

                  <i class="fa fa-long-arrow-right icon-arrow-step current"></i>
                  <span 
                    class="btn list-arrow-step current"
                    data-bs-container="body"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover"
                    data-bs-placement="top"
                    data-bs-content="Enter your personal data so we can process your order."
                    >
                    CHECKOUT
                  </span>

                  <i class="fa fa-long-arrow-right icon-arrow-step"></i>
                  <span 
                    class="btn list-arrow-step"
                    data-bs-container="body"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover"
                    data-bs-placement="top"
                    data-bs-content="Order complete. View your oder summary and our payment information."
                    >
                      ORDER COMPLETE
                   </span>

                  <i class="fa fa-long-arrow-right icon-arrow-step"></i>
                  <span 
                    class="btn list-arrow-step"
                    data-bs-container="body"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover"
                    data-bs-placement="top"
                    data-bs-content="Make payment by transfering your order total to one of our bank accounts."
                    >
                    MAKE PAYMENT
                  </span>

                  <i class="fa fa-long-arrow-right icon-arrow-step"></i>
                  <span 
                    class="btn list-arrow-step"
                    data-bs-container="body"
                    data-bs-toggle="popover"
                    data-bs-trigger="hover"
                    data-bs-placement="top"
                    data-bs-content="We ship and deliver your products."
                    >
                    DELIVERY
                  </span>
              </div>
          </div>
      </div>
      <div class="row mt-5">
        <div class="col-md-2"></div>
        <div class="col-md-4">
            <h2 class="login-head mb-3">
                <i class="fa fa-user-circle"></i> 
                <span class="login-head-sub set-cursor-pointer btn-show-form-login">Please login</span> 
                if you already have an account.
            </h2>
            <div class="show-form-login">
            <div class="form-group">
                <label class="label-form-login">Username or email <span class="text-danger">*</span></label>
                <input type="text" class="form-control c-form-control" placeholder="Enter username or email">
            </div>
            <div class="form-group">
                <label class="label-form-login">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control c-form-control" placeholder="Password">
            </div>
            <button type="submit" class="btn btn-login">LOGIN</button>
            </div>

            <!-- Billing -->
            <div class="c-alert checkout-validation">
                <div><span class="text-danger">*</span> <span class="set-bold">Billing First name</span> is a required field.</div> 
                <div><span class="text-danger">*</span> <span class="set-bold">Shipping First name</span> is a required field.</div>
            </div>
            <h2 class="title-billing-detail">BILLING DETAILS</h2>
            
            <div class="show-form-ship-own">
              <div class="row">
                  <div class="form-group col-md-6">
                    <label class="label-form-billing">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control c-form-control" placeholder="First Name">
                  </div>
                  <div class="form-group col-md-6">
                    <label class="label-form-billing">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control c-form-control" placeholder="Last Name">
                  </div>
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Company Name (Optional)</label>
                  <input type="text" class="form-control c-form-control" placeholder="Company Name">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Country <span class="text-danger">*</span></label>
                  <select class="form-control c-form-control">
                      <option value="">Indonesia</option>
                  </select>
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Street address <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Street address">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Town / City <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Town / City">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">State <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="State">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Zip Code <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Zip Code">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Phone Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Phone Number">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Email address<span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Email address">
              </div>
            </div>
            <div class="form-group checkbox-wrap">
                <div class="form-check">
                  <input class="form-check-input btn-ship-different set-cursor-pointer" type="checkbox" id="checkbox_ship_different">
                  <label class="form-check-label label-check set-cursor-pointer" for="checkbox_ship_different">
                    SHIP TO DIFFERENT ADDRESS
                  </label>
                </div>
            </div>
            <!-- Ship Different -->
            <div class="show-form-ship-different">
              <div class="row">
                  <div class="form-group col-md-6">
                    <label class="label-form-billing">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control c-form-control" placeholder="First Name">
                  </div>
                  <div class="form-group col-md-6">
                    <label class="label-form-billing">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control c-form-control" placeholder="Last Name">
                  </div>
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Company Name (Optional)</label>
                  <input type="text" class="form-control c-form-control" placeholder="Company Name">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Country <span class="text-danger">*</span></label>
                  <select class="form-control c-form-control">
                      <option value="">Indonesia</option>
                  </select>
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Street address <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Street address">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Town / City <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Town / City">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">State <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="State">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Zip Code <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Zip Code">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Phone Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Phone Number">
              </div>
              <div class="form-group">
                  <label class="label-form-billing">Email address<span class="text-danger">*</span></label>
                  <input type="text" class="form-control c-form-control" placeholder="Email address">
              </div>
            </div>
            <!-- Ship Different -->
            <div class="form-group">
                <label class="label-form-billing">Order notes (optional)</label>
                <textarea  class="form-control c-form-control" cols="30" rows="3"></textarea>
            </div>
            <!-- End billing -->
        </div>
        <div class="col-md-4">
            <!-- Order review -->
            <h2 class="title-order-review">ORDER REVIEW</h2>          
            <table class="table table-order">
                <thead>
                  <tr>
                    <th class="text-left thead-orders">PRODUCT</th>
                    <th class="text-right thead-orders">SUBTOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-left tbody-orders">Smartwatch Wood Edition x 1</td>
                    <td class="text-right tbody-orders">Rp 1.299.000</td>
                  </tr>
                  <tr>
                    <td class="text-left tbody-orders">T-Shirt Panda x 1</td>
                    <td class="text-right tbody-orders">Rp 399.000</td>
                  </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-left tfoot-orders">SUBTOTAL</th>
                        <th class="text-right tfoot-orders">Rp 1.698.000</th>
                    </tr>
                    <tr class="set-border-bottom-table">
                        <th class="text-left tfoot-orders">TOTAL</th>
                        <th class="text-right th-total-orders tfoot-orders">Rp 1.698.000</th>
                    </tr>
                </tfoot>
            </table>
            <!-- End Order review -->

            <!-- payment methods -->
            <h2 class="title-payment">PAYMENT METHODS</h2>     
            <div class="choose-payment">
                  <div class="form-check">
                    <input class="form-check-input set-cursor-pointer radio-show-direct-bank" type="radio" name="exampleRadios" id="directbank" value="option1" checked>
                    <label class="form-check-label label-form-billing set-cursor-pointer" for="directbank">
                      Direct Bank Transfer
                    </label>
                  </div>
                  <div class="radio-item-payment show-direct-bank">
                    <img src="assets/images/polygon.svg" class="polygon-payment">
                    Make your payment directly into our bank account. Please use your Order ID or Unique Code as the payment reference. Your order won't be shipped until the funds have cleared in our account.
                  </div>
                  <div class="form-check">
                    <input class="form-check-input set-cursor-pointer radio-show-paypal" type="radio" name="exampleRadios" id="paypal" value="option2">
                    <label class="form-check-label label-form-billing set-cursor-pointer" for="paypal">
                      Paypal
                    </label>
                  </div>
                  <div class="radio-item-payment show-paypal">
                    <img src="assets/images/polygon.svg" class="polygon-payment">
                    Make your payment directly into our bank account. Please use your Order ID or Unique Code as the payment reference. Your order won't be shipped until the funds have cleared in our account.
                  </div>
                  <div class="form-check">
                    <input class="form-check-input set-cursor-pointer radio-show-credit-card" type="radio" name="exampleRadios" id="creditcard" value="option3">
                    <label class="form-check-label label-form-billing set-cursor-pointer" for="creditcard">
                      Credit Card
                    </label>
                  </div>
                  <div class="radio-item-payment show-credit-card">
                    <img src="assets/images/polygon.svg" class="polygon-payment">
                    Make your payment directly into our bank account. Please use your Order ID or Unique Code as the payment reference. Your order won't be shipped until the funds have cleared in our account.
                  </div>
            </div>
            <div class="personal-data-desc">
                Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <span class="set-bold">privacy policy</span>. Once you place an order, you have read and agreed to our <span class="set-bold">terms and conditions</span>.
            </div>
            <button class="btn btn-order-now">PLACE ORDER NOW</button>
            <!-- end payment methods -->
        </div>
        <div class="col-md-2"></div>
      </div>
    </section>