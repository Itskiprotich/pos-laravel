@push('css_or_js')
<link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>
@endpush
<ul class="list-group list-group-flush" id="productList">
    @foreach($products as $i)
        <li class="list-group-item">
            <a href="#" data-product-id="{{ $i->id }}" class="add-to-cart-link">
                {{ $i['name'] }}
            </a>
        </li>
    @endforeach
</ul>

<script>
    "use strict";

    $('#productList').on('click', '.add-to-cart-link', function(e) {
        e.preventDefault();
        var productName = $(this).text();
        $('.search-bar-input-mobile').val(productName);
        $('.search-bar-input').val(productName);
        var productId = $(this).data('product-id');
        addToCart(productId);
    });
</script>
