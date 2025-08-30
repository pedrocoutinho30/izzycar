   <div class="modal fade " id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">

               <div class="modal-header">
                   <h5 class="modal-title " id="contactModalLabel">Pedir mais informações</h5>
                   <button type="button" class="btn-close bg-finance"  data-bs-dismiss="modal" aria-label="Fechar"></button>
               </div>

               <div class="modal-body">
                   <form action="{{ route('contact.vehicle') }}" method="POST">
                       @csrf

                       <div class="mb-3">
                           <label for="name" class="form-label">Nome</label>
                           <input type="text" class="form-control" name="name" id="name" required>
                       </div>

                       <div class="mb-3">
                           <label for="phone" class="form-label">Telemóvel</label>
                           <input type="tel" class="form-control" name="phone" id="phone" required>
                       </div>

                       <div class="mb-3">
                           <label for="email" class="form-label">Email</label>
                           <input type="email" class="form-control" name="email" id="email">
                       </div>

                       <div class="mb-3">
                           <label for="message" class="form-label">Mensagem</label>
                           <textarea class="form-control" name="message" id="message" rows="3" required></textarea>
                       </div>

                       <!-- Campo oculto com o URL do anúncio -->
                       <input type="hidden" name="url" value="{{ request()->fullUrl() }}">

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="contactSubmitBtn">Enviar</button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const form = document.querySelector('#contactModal form');
                            const submitBtn = document.getElementById('contactSubmitBtn');
                            if (form && submitBtn) {
                                form.addEventListener('submit', function () {
                                    submitBtn.disabled = true;
                                    submitBtn.innerText = 'A enviar...';
                                });
                            }
                        });
                    </script>   </form>
               </div>

           </div>
       </div>
   </div>
   @push('styles')

   <style>
       #contactModal .modal-content {
           background-color: var(--primary-color);
           color: white;
           /* para texto ficar legível */
       }

       #contactModal .form-label {
           color: white;
           /* labels em branco */
       }

       .modal-backdrop.show {
           backdrop-filter: blur(20px);
           /* Ajuste a intensidade aqui */
           background-color: rgba(0, 0, 0, 0.8);
           /* mantém escurecimento */
       }

       #contactModal .form-control {
           background-color: var(--secondary-color);
           border: 1px solid var(--accent-color);
           color: white;
       }

       #contactModal .form-control:focus {
           background-color: rgba(255, 255, 255, 0.15);
           border-color: var(--accent-color);
           color: white;
       }

       #contactModal .btn-primary {
           background-color: var(--accent-color);
           border: none;
       }
   </style>
   @endpush