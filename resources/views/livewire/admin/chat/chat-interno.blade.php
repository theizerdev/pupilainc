<div wire:poll.5s="pollMessages">
    @push('styles')
    <link rel="stylesheet" href="/materialize/assets/vendor/css/pages/app-chat.css" />
    <style>
        /* WhatsApp-style chat background */
        .app-chat { --bs-chat-bg: #efeae2; }
        [data-bs-theme=dark] .app-chat { --bs-chat-bg: #0b141a; }

        .chat-history-body {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c8c3ba' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        [data-bs-theme=dark] .chat-history-body {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* WhatsApp-style bubbles */
        .wa-bubble {
            position: relative;
            max-width: 65%;
            padding: 6px 12px 22px 12px;
            border-radius: 8px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.13);
            word-wrap: break-word;
            line-height: 1.45;
            font-size: 0.9rem;
        }
        .wa-bubble-out {
            background-color: #d9fdd3;
            border-top-right-radius: 0;
            margin-left: auto;
        }
        [data-bs-theme=dark] .wa-bubble-out {
            background-color: #005c4b;
            color: #e9edef;
        }
        .wa-bubble-in {
            background-color: #ffffff;
            border-top-left-radius: 0;
        }
        [data-bs-theme=dark] .wa-bubble-in {
            background-color: #202c33;
            color: #e9edef;
        }

        /* Tail / triangle */
        .wa-bubble-out::before {
            content: '';
            position: absolute;
            top: 0;
            right: -8px;
            width: 0;
            height: 0;
            border-left: 8px solid #d9fdd3;
            border-bottom: 8px solid transparent;
        }
        [data-bs-theme=dark] .wa-bubble-out::before {
            border-left-color: #005c4b;
        }
        .wa-bubble-in::before {
            content: '';
            position: absolute;
            top: 0;
            left: -8px;
            width: 0;
            height: 0;
            border-right: 8px solid #ffffff;
            border-bottom: 8px solid transparent;
        }
        [data-bs-theme=dark] .wa-bubble-in::before {
            border-right-color: #202c33;
        }

        /* Message meta (time + checks) inside bubble */
        .wa-meta {
            position: absolute;
            bottom: 4px;
            right: 10px;
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 0.7rem;
            color: #667781;
            white-space: nowrap;
        }
        [data-bs-theme=dark] .wa-meta {
            color: rgba(233,237,239,0.6);
        }
        .wa-meta .wa-check {
            font-size: 1rem;
            line-height: 1;
        }
        .wa-meta .wa-check.read {
            color: #53bdeb;
        }
        .wa-meta .wa-check.sent {
            color: #667781;
        }
        [data-bs-theme=dark] .wa-meta .wa-check.sent {
            color: rgba(233,237,239,0.5);
        }

        /* Sender name inside received bubbles */
        .wa-sender-name {
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 1px;
            color: #1fa855;
        }
        [data-bs-theme=dark] .wa-sender-name {
            color: #06cf9c;
        }

        /* Message row */
        .wa-message-row {
            display: flex;
            margin-bottom: 4px;
            padding-inline: 4%;
        }
        .wa-message-row.out {
            justify-content: flex-end;
        }
        .wa-message-row.in {
            justify-content: flex-start;
        }

        /* Date separator */
        .wa-date-separator {
            display: flex;
            justify-content: center;
            margin: 14px 0;
        }
        .wa-date-separator span {
            background: #e1f2fb;
            color: #54656f;
            font-size: 0.76rem;
            padding: 5px 14px;
            border-radius: 8px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.08);
            font-weight: 500;
        }
        [data-bs-theme=dark] .wa-date-separator span {
            background: #182229;
            color: rgba(233,237,239,0.75);
        }

        /* Chat history override */
        .app-chat .app-chat-history .chat-history-body .chat-history .chat-message {
            margin-block-end: 0 !important;
        }

        /* Empty state */
        .wa-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #667781;
        }
        .wa-empty-state i { color: #c0c9cf; }
        [data-bs-theme=dark] .wa-empty-state { color: rgba(233,237,239,0.5); }
        [data-bs-theme=dark] .wa-empty-state i { color: rgba(233,237,239,0.2); }

        /* Input area WhatsApp style */
        .wa-input-area {
            background: #f0f2f5;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        [data-bs-theme=dark] .wa-input-area {
            background: #202c33;
        }
        .wa-input-area .wa-input {
            flex: 1;
            border: none;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 0.9rem;
            background: #ffffff;
            outline: none;
        }
        [data-bs-theme=dark] .wa-input-area .wa-input {
            background: #2a3942;
            color: #e9edef;
        }
        .wa-input-area .wa-input::placeholder {
            color: #8696a0;
        }
        .wa-send-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: var(--bs-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.15s;
            flex-shrink: 0;
        }
        .wa-send-btn:hover {
            transform: scale(1.08);
        }
    </style>
    @endpush

    <div class="app-chat card overflow-hidden">
        <div class="row g-0">
            {{-- Chat Contacts Sidebar --}}
            <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts" style="position:static;">
                <div class="sidebar-header h-px-75 px-5 border-bottom d-flex align-items-center">
                    <div class="d-flex align-items-center me-6 me-lg-0 w-100">
                        <div class="flex-shrink-0 avatar avatar-online me-4">
                            @if(auth()->user()->avatar)
                                <img class="rounded-circle" src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" />
                            @else
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-grow-1 input-group input-group-sm input-group-merge rounded-pill">
                            <span class="input-group-text" id="basic-addon-search31">
                                <i class="icon-base ri ri-search-line icon-20px"></i>
                            </span>
                            <input type="text" class="form-control chat-search-input" placeholder="Buscar contacto..." wire:model.live.debounce.300ms="search" />
                        </div>
                    </div>
                    <i class="icon-base ri ri-close-line icon-lg cursor-pointer position-absolute top-50 end-0 translate-middle d-lg-none d-block"
                       data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
                </div>
                <div class="sidebar-body" style="height: calc(100vh - 16.4rem); overflow-y: auto;">
                    {{-- Chat List --}}
                    <ul class="list-unstyled chat-contact-list py-2 mb-0" id="chat-list">
                        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                            <h5 class="text-primary mb-0">
                                Conversaciones
                                @if($unreadTotal > 0)
                                    <span class="badge bg-danger rounded-pill ms-2">{{ $unreadTotal }}</span>
                                @endif
                            </h5>
                        </li>

                        @php
                            $hasConversations = $chatUsers->filter(fn($u) => $u['last_message'])->count() > 0;
                        @endphp

                        @if(!$hasConversations)
                            <li class="chat-contact-list-item">
                                <h6 class="text-body-secondary mb-0 text-center py-4">Sin conversaciones aún</h6>
                            </li>
                        @endif

                        @foreach($chatUsers->filter(fn($u) => $u['last_message']) as $user)
                            <li class="chat-contact-list-item mb-1 {{ $selectedUserId == $user['id'] ? 'active' : '' }}"
                                wire:click="selectUser({{ $user['id'] }})" style="cursor:pointer;">
                                <a class="d-flex align-items-center">
                                    <div class="flex-shrink-0 avatar">
                                        @if($user['avatar'])
                                            <img src="{{ Storage::url($user['avatar']) }}" alt="Avatar" class="rounded-circle" />
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ $user['initials'] }}</span>
                                        @endif
                                    </div>
                                    <div class="chat-contact-info flex-grow-1 ms-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="chat-contact-name text-truncate m-0 fw-normal">{{ $user['name'] }}</h6>
                                            @if($user['last_message_time'])
                                                <small class="chat-contact-list-item-time">
                                                    {{ $user['last_message_time']->diffForHumans(null, true) }}
                                                </small>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="chat-contact-status text-truncate">{{ Str::limit($user['last_message'], 35) }}</small>
                                            @if($user['unread_count'] > 0)
                                                <span class="badge bg-danger rounded-pill">{{ $user['unread_count'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Contact List --}}
                    <ul class="list-unstyled chat-contact-list mb-0 py-2" id="contact-list">
                        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                            <h5 class="text-primary mb-0">Contactos</h5>
                        </li>

                        @foreach($chatUsers as $user)
                            <li class="chat-contact-list-item {{ $selectedUserId == $user['id'] ? 'active' : '' }}"
                                wire:click="selectUser({{ $user['id'] }})" style="cursor:pointer;">
                                <a class="d-flex align-items-center">
                                    <div class="flex-shrink-0 avatar">
                                        @if($user['avatar'])
                                            <img src="{{ Storage::url($user['avatar']) }}" alt="Avatar" class="rounded-circle" />
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-{{ ['primary','success','warning','info','danger'][crc32($user['name']) % 5] }}">
                                                {{ $user['initials'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="chat-contact-info flex-grow-1 ms-4">
                                        <h6 class="chat-contact-name text-truncate m-0 fw-normal">{{ $user['name'] }}</h6>
                                        <small class="chat-contact-status text-truncate">{{ $user['role'] }}</small>
                                    </div>
                                </a>
                            </li>
                        @endforeach

                        @if($chatUsers->isEmpty())
                            <li class="chat-contact-list-item">
                                <h6 class="text-body-secondary mb-0 text-center py-4">No se encontraron contactos</h6>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            {{-- /Chat Contacts --}}

            {{-- Empty State --}}
            @if(!$selectedUserId)
                <div class="col app-chat-conversation d-flex align-items-center justify-content-center flex-column" id="app-chat-conversation">
                    <div class="bg-label-primary p-8 rounded-circle">
                        <i class="icon-base ri ri-wechat-line icon-48px"></i>
                    </div>
                    <p class="my-4">Selecciona un contacto para iniciar una conversación.</p>
                    <button class="btn btn-primary app-chat-conversation-btn d-lg-none" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts">
                        Ver Contactos
                    </button>
                </div>
            @else
                {{-- Chat History --}}
                <div class="col app-chat-history" id="app-chat-history">
                    <div class="chat-history-wrapper" style="display:flex; flex-direction:column; height:100%;">
                        {{-- Header --}}
                        <div class="chat-history-header border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex overflow-hidden align-items-center">
                                    <i class="icon-base ri ri-menu-line icon-lg cursor-pointer d-lg-none d-block me-4"
                                       data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts"></i>
                                    <div class="flex-shrink-0 avatar avatar-online">
                                        @if($selectedUser->avatar)
                                            <img src="{{ Storage::url($selectedUser->avatar) }}" alt="Avatar" class="rounded-circle" />
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($selectedUser->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="chat-contact-info flex-grow-1 ms-4">
                                        <h6 class="m-0 fw-normal">{{ $selectedUser->name }}</h6>
                                        <small class="user-status text-body">{{ $selectedUser->roles->first()?->name ?? 'Usuario' }}</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" id="toggle-chat-sound" title="Alternar sonido de notificaciones">
                                        <i class="ri ri-volume-up-line fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Messages Body --}}
                        <div class="chat-history-body" id="chat-history-body" style="flex:1; overflow-y:auto; padding: 12px 0;">
                            @if(empty($messages))
                                <div class="wa-empty-state">
                                    <i class="icon-base ri ri-chat-smile-2-line" style="font-size:4rem;"></i>
                                    <p class="mt-3 mb-0">No hay mensajes aún</p>
                                    <small>Envía el primer mensaje para iniciar la conversación</small>
                                </div>
                            @else
                                @php $lastDate = null; @endphp
                                @foreach($messages as $msg)
                                    {{-- Date separator --}}
                                    @if($lastDate !== $msg['date'])
                                        <div class="wa-date-separator">
                                            <span>{{ $msg['date'] }}</span>
                                        </div>
                                        @php $lastDate = $msg['date']; @endphp
                                    @endif

                                    {{-- Message bubble --}}
                                    <div class="wa-message-row {{ $msg['is_mine'] ? 'out' : 'in' }} {{ !$msg['is_mine'] && !$msg['is_read'] ? 'unread-msg cursor-pointer' : '' }}"
                                         @if(!$msg['is_mine'] && !$msg['is_read']) wire:click="markAsRead" title="Clic para marcar como leído" @endif>

                                        @if(!$msg['is_mine'] && !$msg['is_read'])
                                            <div class="d-flex align-items-center me-2">
                                                <span class="badge bg-primary p-1 rounded-circle" style="width: 8px; height: 8px;"></span>
                                            </div>
                                        @endif

                                        <div class="wa-bubble {{ $msg['is_mine'] ? 'wa-bubble-out' : 'wa-bubble-in' }}" style="{{ !$msg['is_mine'] && !$msg['is_read'] ? 'font-weight: 500; box-shadow: 0 1px 4px rgba(105,108,255,0.4); border: 1px solid var(--bs-primary);' : '' }}">
                                            @if(!$msg['is_mine'])
                                                <div class="wa-sender-name">{{ $msg['sender_name'] }}</div>
                                            @endif
                                            <span>{{ $msg['message'] }}</span>
                                            <span class="wa-meta">
                                                <span>{{ $msg['time'] }}</span>
                                                @if($msg['is_mine'])
                                                    @if($msg['is_read'])
                                                        <i class="ri ri-check-double-fill wa-check read"></i>
                                                    @else
                                                        <i class="ri ri-check-double-fill wa-check sent"></i>
                                                    @endif
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        {{-- Input Area --}}
                        <form wire:submit.prevent="sendMessage" class="wa-input-area">
                            <input type="text"
                                   class="wa-input"
                                   placeholder="Escribe un mensaje"
                                   wire:model="messageText"
                                   wire:keydown.enter.prevent="sendMessage"
                                   autocomplete="off" />
                            <button type="submit" class="wa-send-btn" title="Enviar">
                                <i class="ri ri-send-plane-fill" style="font-size:1.2rem;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="app-overlay"></div>
        </div>
    </div>

    {{-- Input oculto para que el notificador global sepa qué chat está activo --}}
    <input type="hidden" id="chat-selected-user-id" value="{{ $selectedUserId }}">

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const scrollToBottom = () => {
                const chatBody = document.getElementById('chat-history-body');
                if (chatBody) {
                    chatBody.scrollTop = chatBody.scrollHeight;
                }
            };

            let readTimer = null;

            const startReadTimer = () => {
                clearTimeout(readTimer);
                // Si hay mensajes no leídos, marcar como leído después de 3 segundos
                const hasUnread = document.querySelector('.unread-msg');
                if (hasUnread) {
                    readTimer = setTimeout(() => {
                        @this.markAsRead();
                    }, 3000);
                }
            };

            Livewire.on('scrollToBottom', () => {
                setTimeout(scrollToBottom, 100);
            });

            Livewire.hook('morph.updated', ({ el }) => {
                if (el.id === 'chat-history-body') {
                    scrollToBottom();
                }
                if (el.classList && el.classList.contains('app-chat')) {
                    startReadTimer();
                }
            });

            scrollToBottom();
            startReadTimer();

            // Sincronizar contadores globales a locales
            window.addEventListener('update-chat-unread-counts', () => {
                @this.pollMessages();
            });

            // Toggle de sonido
            const initSoundToggle = () => {
                const btn = document.getElementById('toggle-chat-sound');
                if (!btn) return;

                const icon = btn.querySelector('i');
                const isEnabled = localStorage.getItem('chatSoundEnabled') !== 'false';

                if (isEnabled) {
                    icon.className = 'ri ri-volume-up-line fs-5';
                    btn.classList.add('text-primary');
                    btn.classList.remove('text-muted');
                } else {
                    icon.className = 'ri ri-volume-mute-line fs-5';
                    btn.classList.add('text-muted');
                    btn.classList.remove('text-primary');
                }

                btn.onclick = () => {
                    const current = localStorage.getItem('chatSoundEnabled') !== 'false';
                    localStorage.setItem('chatSoundEnabled', !current);
                    initSoundToggle(); // Refrescar icono
                };
            };

            initSoundToggle();
            Livewire.hook('morph.updated', ({ el }) => {
                if (el.id === 'toggle-chat-sound') {
                    initSoundToggle();
                }
            });
        });
    </script>
    @endpush
</div>
