@extends('frontend.layouts.master')

@section('title')
{{$settings->site_name}} || Checkout
@endsection

@section('content')
<section id="wsus__breadcrumb">
    <div class="wsus_breadcrumb_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h4>check out</h4>
                    <ul>
                        <li><a href="{{route('home')}}">home</a></li>
                        <li><a href="javascript:;">check out</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="wsus__cart_view">
    <div class="container">
        <div class="row">
            <!-- LEFT: Shipping + Address -->
            <div class="col-xl-8 col-lg-7">
                <div class="wsus__check_form">
                    <h5>Shipping Details</h5>

                    <div class="row">
                        @foreach ($addresses as $address)
                        <div class="col-xl-6">
                            <div class="wsus__checkout_single_address">
                                <div class="form-check">
                                    <input class="form-check-input shipping_address" data-id="{{$address->id}}"
                                        type="radio" name="flexRadioDefault" id="address{{$address->id}}">
                                    <label class="form-check-label" for="address{{$address->id}}">
                                        Select Address
                                    </label>
                                </div>
                                <ul>
                                    <li><span>Name :</span> {{$address->name}}</li>
                                    <li><span>Phone :</span> {{$address->phone}}</li>
                                    <li><span>Email :</span> {{$address->email}}</li>
                                    <li><span>Country :</span> {{$address->country}}</li>
                                    <li><span>City :</span> {{$address->city}}</li>
                                    <li><span>Zip :</span> {{$address->zip}}</li>
                                    <li><span>Address :</span> {{$address->address}}</li>
                                </ul>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <hr class="my-4">
                    <h5>Add New Address</h5>
                    <form action="{{route('user.checkout.address.create')}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="wsus__check_single_form">
                                    <input type="text" name="name" placeholder="Name *" value="{{old('name')}}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wsus__check_single_form">
                                    <input type="text" name="phone" placeholder="Phone *" value="{{old('phone')}}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wsus__check_single_form">
                                    <input type="email" name="email" placeholder="Email *" value="{{old('email')}}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wsus__check_single_form">
                                    <select class="select_2" name="country">
                                        <option value="">Country / Region *</option>
                                        @foreach (config('settings.country_list') as $key => $county)
                                        <option {{$county === old('country') ? 'selected' : ''}} value="{{$county}}">
                                            {{$county}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wsus__check_single_form">
                                    <input type="text" name="state" placeholder="State *" value="{{old('state')}}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wsus__check_single_form">
                                    <input type="text" name="city" placeholder="City *" value="{{old('city')}}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="wsus__check_single_form">
                                    <input type="text" name="zip" placeholder="Zip *" value="{{old('zip')}}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="wsus__check_single_form">
                                    <input type="text" name="address" placeholder="Address *"
                                        value="{{old('address')}}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="wsus__check_single_form">
                                    <button type="submit" class="btn btn-primary">Save Address</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT: Summary + Payment -->
            <div class="col-xl-4 col-lg-5">
                <div class="wsus__order_details" id="sticky_sidebar">
                    <p class="wsus__product">Shipping Methods</p>
                    @foreach ($shippingMethods as $method)
                    @if (
                    $method->type === 'flat_cost' || ($method->type === 'min_cost' && getCartTotal() >=
                    $method->min_cost)
                    )
                    <div class="form-check">
                        <input class="form-check-input shipping_method" type="radio" name="shipping_method"
                            id="method{{$method->id}}" value="{{$method->id}}" data-id="{{$method->cost}}">
                        <label class="form-check-label" for="method{{$method->id}}">
                            {{$method->name}} <br>
                            <small>Cost: ({{$settings->currency_icon}}{{$method->cost}})</small>
                        </label>
                    </div>
                    @endif
                    @endforeach

                    <p class="wsus__product mt-4">Payment Methods</p>
                    <div class="form-check">
                        <input class="form-check-input payment_method" type="radio" name="payment_method"
                            id="payment_cod" value="cod">
                        <label class="form-check-label" for="payment_cod">Cash On Delivery</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input payment_method" type="radio" name="payment_method"
                            id="payment_visa" value="visa">
                        <label class="form-check-label" for="payment_visa">Visa / MasterCard</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input payment_method" type="radio" name="payment_method"
                            id="payment_khqr" value="khqr">
                        <label class="form-check-label" for="payment_khqr">KHQR Payment</label>
                    </div>

                    <div class="wsus__order_details_summery mt-3">
                        <p>Subtotal: <span>{{$settings->currency_icon}}{{getCartTotal()}}</span></p>
                        <p>Shipping(+): <span id="shipping_fee">{{$settings->currency_icon}}0</span></p>
                        <p>Coupon(-): <span>{{$settings->currency_icon}}{{getCartDiscount()}}</span></p>
                        <p><b>Total:</b> <span><b id="total_amount"
                                    data-id="{{getMainCartTotal()}}">{{$settings->currency_icon}}{{getMainCartTotal()}}</b></span>
                        </p>
                    </div>

                    <div class="terms_area">
                        <div class="form-check">
                            <input class="form-check-input agree_term" type="checkbox" id="agree_terms" checked>
                            <label class="form-check-label" for="agree_terms">
                                I have read and agree to the <a href="#">terms and conditions *</a>
                            </label>
                        </div>
                    </div>

                    <form id="checkOutForm">
                        <input type="hidden" name="shipping_method_id" id="shipping_method_id">
                        <input type="hidden" name="shipping_address_id" id="shipping_address_id">
                        <input type="hidden" name="payment_method" id="payment_method">
                    </form>

                    <a href="" id="submitCheckoutForm" class="common_btn">Place Order</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ✅ KHQR Modal -->
<div class="modal fade" id="khqrModal" tabindex="-1" aria-labelledby="khqrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title" id="khqrModalLabel">Scan KHQR to Complete Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qrLoadingSpinner" style="display:none;">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                    <p>Generating QR Code...</p>
                </div>
                <div id="qrContent" style="display:none;">
                    <img id="khqrImage" src="" alt="KHQR Code"
                        style="width:300px;height:300px;border:1px solid #ddd;padding:10px;border-radius:8px;">
                    <p id="qrTimer" class="mt-3 text-danger fw-bold"></p>
                    <p class="text-muted">Use any KHQR-supported bank app to scan</p>
                    <p class="text-success mt-2"><small>Amount: <span id="qrAmount"></span></small></p>
                </div>
                <div id="qrError" style="display:none;">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p class="text-danger" id="qrErrorMessage"></p>
                    <button class="btn btn-primary btn-sm" onclick="location.reload()">Try Again</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let qrTimerInterval;
    let qrTimeLeft = 120;

    function startQrTimer() {
        qrTimeLeft = 120;
        clearInterval(qrTimerInterval);
        qrTimerInterval = setInterval(function() {
            qrTimeLeft--;
            $('#qrTimer').text('QR expires in ' + qrTimeLeft + 's');
            if (qrTimeLeft <= 0) {
                clearInterval(qrTimerInterval);
                $('#qrTimer').text('QR expired! Please close and try again.');
                $('#khqrImage').css('opacity', '0.4');
            }
        }, 1000);
    }

    // shipping + address selection
    $('.shipping_method').on('click', function() {
        let shippingFee = $(this).data('id');
        let currentTotal = $('#total_amount').data('id');
        let totalAmount = parseFloat(currentTotal) + parseFloat(shippingFee);
        $('#shipping_method_id').val($(this).val());
        $('#shipping_fee').text("{{$settings->currency_icon}}" + shippingFee);
        $('#total_amount').text("{{$settings->currency_icon}}" + totalAmount);
        $('#total_amount').attr('data-id', totalAmount);
    });

    $('.shipping_address').on('click', function() {
        $('#shipping_address_id').val($(this).data('id'));
    });

    $('.payment_method').on('click', function() {
        $('#payment_method').val($(this).val());
    });

    // ✅ KHQR generator with base64 support
    function generateKHQR(amount) {
        // Show modal with loading state
        $('#qrLoadingSpinner').show();
        $('#qrContent').hide();
        $('#qrError').hide();
        $('#khqrModal').modal('show');

        $.ajax({
            url: "{{ route('user.checkout.khqr-generate') }}",
            method: 'POST',
            data: {
                // amount: 0.01 // for testing
                amount: amount
            },
            success: function(res) {
                console.log('KHQR Response:', res); // Debug log

                if (res.status === 'success' && res.qr_image_url) {
                    // Hide loading, show QR content
                    $('#qrLoadingSpinner').hide();
                    $('#qrContent').show();

                    // Set the base64 image
                    $('#khqrImage')
                        .attr('src', res.qr_image_url)
                        .css('opacity', '1')
                        .on('error', function() {
                            console.error('Image failed to load');
                            showQrError('Failed to load QR image. Please try again.');
                        });

                    $('#qrAmount').text("{{$settings->currency_icon}}" + amount);
                    $('#qrTimer').text('QR expires in 120s');
                    startQrTimer();
                    $('#submitCheckoutForm').text('Place Order');
                } else {
                    showQrError(res.message || 'Failed to generate KHQR');
                    $('#submitCheckoutForm').text('Place Order');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr); // Debug log
                let msg = 'Server error. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                showQrError(msg);
                $('#submitCheckoutForm').text('Place Order');
            }
        });
    }

    function showQrError(message) {
        $('#qrLoadingSpinner').hide();
        $('#qrContent').hide();
        $('#qrError').show();
        $('#qrErrorMessage').text(message);
        toastr.error(message);
    }

    // Place Order button
    $('#submitCheckoutForm').on('click', function(e) {
        e.preventDefault();
        let payment = $('#payment_method').val();

        if (!$('#shipping_method_id').val()) return toastr.error('Shipping method is required');
        if (!$('#shipping_address_id').val()) return toastr.error('Shipping address is required');
        if (!$('#agree_terms').prop('checked')) return toastr.error('You must agree to terms');
        if (!payment) return toastr.error('Please select a payment method');

        if (payment === 'khqr') {
            let total = $('#total_amount').data('id');
            generateKHQR(total);
        } else {
            $.ajax({
                url: "{{route('user.checkout.form-submit')}}",
                method: 'POST',
                data: $('#checkOutForm').serialize(),
                beforeSend: function() {
                    $('#submitCheckoutForm').html(
                        '<i class="fas fa-spinner fa-spin"></i> Processing...');
                },
                success: function(data) {
                    if (data.status === 'success') {
                        $('#submitCheckoutForm').text('Place Order');
                        window.location.href = data.redirect_url;
                    }
                },
                error: function() {
                    $('#submitCheckoutForm').text('Place Order');
                    toastr.error('Something went wrong!');
                }
            });
        }
    });

    // Close modal event
    $('#khqrModal').on('hidden.bs.modal', function() {
        clearInterval(qrTimerInterval);
        $('#khqrImage').attr('src', '');
    });
});
</script>
@endpush