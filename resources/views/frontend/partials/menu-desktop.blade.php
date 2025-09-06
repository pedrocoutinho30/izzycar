   <div class="collapse navbar-collapse" id="navbarNav">
       <ul class="navbar-nav ms-auto">
           <li class="nav-item" hidden>
               <a class="nav-link active" href=" "></a>
           </li>
           @foreach($menus->where('parent_id', null) as $menu)
           @php
           $hasChildren = $menu->children_active->count() > 0;


           @endphp
           @if(!$hasChildren)

           <li class="nav-item">
               <a class="nav-link" href="{{ $menu->url }}">{{ $menu->title }}</a>
           </li>
           @else


           <li class="nav-item dropdown">
               <a class="nav-link dropdown-toggle" href="#" id="navbarLightDropdownMenuLink-{{ $menu->id }}" role="button" data-bs-toggle="dropdown" aria-expanded="false"> {{ $menu->title }}</a>


               <ul class="dropdown-menu dropdown-menu-light" aria-labelledby="navbarLightDropdownMenuLink-{{ $menu->id }}">
                   @foreach($menu->children_active as $child)

                   <li><a class="dropdown-item" href="{{ $child->url }}">{{$child->title}}</a></li>
                   @endforeach
               </ul>
           </li>
           @endif
           @endforeach
       </ul>
   </div>