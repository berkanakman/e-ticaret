@php
    $user = auth('admin')->user();
    $isSuper = $user && $user->hasRole('superadmin');
    $isAdmin = $user && ($user->hasRole('admin') || $isSuper);
@endphp

<div class="list-group list-group-flush">
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ active('admin.dashboard') }}">
        <i class="bi bi-speedometer2 me-2"></i>
        <span>Dashboard</span>
    </a>

    @if($isAdmin)
        <div class="accordion" id="accordionProductManagement">
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="headingProducts">
                    <button class="accordion-button collapsed px-3 py-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseProducts"
                            aria-expanded="false" aria-controls="collapseProducts">
                        <i class="bi bi-box-seam me-2"></i>
                        Ürün Yönetimi
                    </button>
                </h2>

                <div id="collapseProducts"
                     class="accordion-collapse collapse {{ (str(request()->route()?->getName() ?? '')->startsWith('admin.categories') || str(request()->route()?->getName() ?? '')->startsWith('admin.attributes') || str(request()->route()?->getName() ?? '')->startsWith('admin.products')) ? 'show' : '' }}"
                     aria-labelledby="headingProducts" data-bs-parent="#accordionProductManagement">
                    <div class="accordion-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action ps-5 {{ active('admin.categories') }}">
                                <i class="bi bi-tags me-2"></i> Kategoriler
                            </a>
                            <a href="{{ route('admin.attributes.index') }}" class="list-group-item list-group-item-action ps-5 {{ active('admin.attributes') }}">
                                <i class="bi bi-palette me-2"></i> Özellikler
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="list-group-item list-group-item-action ps-5 {{ active('admin.products') }}">
                                <i class="bi bi-box me-2"></i> Ürünler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isAdmin)
        <a href="{{ route('admin.orders.index') }}" class="list-group-item list-group-item-action d-flex align-items-center {{ active('admin.orders') }}">
            <i class="bi bi-cart-check me-2"></i>
            Siparişler
        </a>
    @endif

    @if($isSuper)
        <div class="accordion mt-2" id="accordionUserManagement">
            <div class="accordion-item border-0">
                <h2 class="accordion-header" id="headingUsers">
                    <button class="accordion-button collapsed px-3 py-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseUsers"
                            aria-expanded="false" aria-controls="collapseUsers">
                        <i class="bi bi-people me-2"></i>
                        Kullanıcı Yönetimi
                    </button>
                </h2>
                <div id="collapseUsers"
                     class="accordion-collapse collapse {{ (str(request()->route()?->getName() ?? '')->startsWith('admin.admins') || str(request()->route()?->getName() ?? '')->startsWith('admin.roles') || str(request()->route()?->getName() ?? '')->startsWith('admin.permissions')) ? 'show' : '' }}"
                     aria-labelledby="headingUsers" data-bs-parent="#accordionUserManagement">
                    <div class="accordion-body p-0">
                        <a href="{{ route('admin.admins.index') }}" class="list-group-item list-group-item-action ps-5 {{ active('admin.admins') }}">
                            <i class="bi bi-shield-lock me-2"></i> Yöneticiler
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="list-group-item list-group-item-action ps-5 {{ active('admin.roles') }}">
                            <i class="bi bi-person-badge me-2"></i> Roller
                        </a>
                        <a href="{{ route('admin.permissions.index') }}" class="list-group-item list-group-item-action ps-5 {{ active('admin.permissions') }}">
                            <i class="bi bi-key me-2"></i> İzinler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
