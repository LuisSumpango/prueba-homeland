@if($products->hasPages())
    <div class="pagination">
        {{ $products->appends(['sort' => request('sort'), 'order' => request('order')])->links() }}
    </div>
@endif