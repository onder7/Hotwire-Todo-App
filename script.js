// Hotwire kütüphanelerini farklı CDN'den yükle
import 'https://unpkg.com/@hotwired/turbo@^8.0.0/dist/turbo.es2017-esm.js'

// Stimulus'u ayrı olarak yükle
const stimulus = await import('https://unpkg.com/@hotwired/stimulus@^3.0.0/dist/stimulus.js')
const { Application, Controller } = stimulus

window.Stimulus = Application.start()

// Turbo events
document.addEventListener('turbo:frame-load', () => {
    console.log('🔄 Turbo frame yüklendi')
})

document.addEventListener('turbo:before-fetch-request', (event) => {
    console.log('📡 AJAX isteği başladı:', event.detail.url)
})

document.addEventListener('turbo:frame-render', () => {
    console.log('✅ Turbo frame render edildi')
})

// Gelişmiş Form Controller
window.Stimulus.register("form", class extends Controller {
    static targets = ["input", "submit", "counter"]
    
    connect() {
        this.updateCounter()
        console.log('📝 Form controller bağlandı')
    }
    
    submit(event) {
        const text = this.inputTarget.value.trim()
        if (!text) {
            event.preventDefault()
            this.inputTarget.focus()
            return
        }
        
        this.submitTarget.disabled = true
        this.submitTarget.textContent = "Ekleniyor..."
        
        setTimeout(() => {
            this.inputTarget.value = ""
            this.submitTarget.disabled = false
            this.submitTarget.textContent = "Ekle"
            this.updateCounter()
            this.inputTarget.focus()
        }, 500)
    }
    
    submitViaKeyboard(event) {
        if (event.ctrlKey && event.key === 'Enter') {
            const text = this.inputTarget.value.trim()
            if (text) {
                this.element.querySelector('form').submit()
            }
        }
    }
    
    updateCounter() {
        if (!this.hasCounterTarget) return
        
        const length = this.inputTarget.value.length
        this.counterTarget.textContent = `${length}/100 karakter`
        
        if (length > 80) {
            this.counterTarget.style.color = '#ff6b6b'
        } else if (length > 60) {
            this.counterTarget.style.color = '#f39c12'
        } else {
            this.counterTarget.style.color = '#999'
        }
        
        // Limit kontrolü
        if (length >= 100) {
            this.submitTarget.disabled = true
            this.counterTarget.textContent = '100/100 karakter (limit aşıldı)'
        } else {
            this.submitTarget.disabled = false
        }
    }
})

// Todo Item Controller
window.Stimulus.register("todo-item", class extends Controller {
    static values = { editing: Boolean }
    
    connect() {
        this.editingValue = false
    }
    
    beforeToggle(event) {
        // Visual feedback
        this.element.style.transform = 'scale(0.98)'
        
        // Debug log
        const todoId = this.element.dataset.todoId
        const isCompleted = this.element.classList.contains('completed')
        console.log(`🔄 Toggle: ID=${todoId}, Current=${isCompleted ? 'completed' : 'pending'}`)
        
        setTimeout(() => {
            this.element.style.transform = 'scale(1)'
        }, 150)
    }
    
    beforeDelete(event) {
        this.element.style.opacity = '0.5'
        this.element.style.transform = 'translateX(-10px)'
    }
    
    edit(event) {
        if (this.editingValue) return
        
        const textEl = this.element.querySelector('.todo-text')
        const currentText = textEl.textContent.trim()
        const todoId = this.element.dataset.todoId
        
        // Edit form oluştur
        const form = document.createElement('form')
        form.action = '?action=edit'
        form.method = 'POST'
        form.setAttribute('data-turbo-frame', 'todo-list')
        
        const input = document.createElement('input')
        input.type = 'text'
        input.value = currentText
        input.className = 'edit-input'
        input.maxLength = 100
        input.required = true
        
        const hiddenId = document.createElement('input')
        hiddenId.type = 'hidden'
        hiddenId.name = 'id'
        hiddenId.value = todoId
        
        const hiddenText = document.createElement('input')
        hiddenText.type = 'hidden'
        hiddenText.name = 'text'
        
        form.appendChild(hiddenId)
        form.appendChild(hiddenText)
        form.appendChild(input)
        
        textEl.replaceWith(form)
        input.focus()
        input.select()
        
        this.editingValue = true
        
        const saveEdit = () => {
            const newText = input.value.trim()
            if (newText && newText !== currentText) {
                hiddenText.value = newText
                form.submit()
            } else {
                cancelEdit()
            }
        }
        
        const cancelEdit = () => {
            textEl.textContent = currentText
            form.replaceWith(textEl)
            this.editingValue = false
        }
        
        input.addEventListener('blur', saveEdit)
        
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault()
                saveEdit()
            }
            if (e.key === 'Escape') {
                e.preventDefault()
                cancelEdit()
            }
        })
    }
})

// Stats Controller  
window.Stimulus.register("stats", class extends Controller {
    static targets = ["display"]
    
    connect() {
        this.updateAnimation()
    }
    
    clearCompleted() {
        if (this.hasDisplayTarget) {
            this.displayTarget.style.opacity = '0.5'
        }
        
        // Konfetti animasyonu
        this.showConfetti()
    }
    
    updateAnimation() {
        if (this.hasDisplayTarget) {
            this.displayTarget.style.animation = 'fadeIn 0.5s ease'
        }
    }
    
    showConfetti() {
        // Basit konfetti efekti
        const confetti = document.createElement('div')
        confetti.innerHTML = '🎉'
        confetti.style.position = 'fixed'
        confetti.style.top = '20px'
        confetti.style.right = '20px'
        confetti.style.fontSize = '24px'
        confetti.style.zIndex = '1000'
        confetti.style.animation = 'bounce 0.6s ease'
        
        document.body.appendChild(confetti)
        
        setTimeout(() => {
            document.body.removeChild(confetti)
        }, 1000)
    }
})

// Celebration Controller
window.Stimulus.register("celebration", class extends Controller {
    animate() {
        this.element.style.animation = 'bounce 0.6s ease'
        
        // Rastgele emoji efekti
        const emojis = ['🎉', '🎊', '✨', '🌟', '🎈']
        const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)]
        
        const emojiEl = document.createElement('div')
        emojiEl.innerHTML = randomEmoji
        emojiEl.style.position = 'absolute'
        emojiEl.style.fontSize = '30px'
        emojiEl.style.animation = 'floatUp 2s ease-out'
        emojiEl.style.left = '50%'
        emojiEl.style.transform = 'translateX(-50%)'
        
        this.element.style.position = 'relative'
        this.element.appendChild(emojiEl)
        
        setTimeout(() => {
            this.element.style.animation = ''
            if (this.element.contains(emojiEl)) {
                this.element.removeChild(emojiEl)
            }
        }, 2000)
    }
})

// Keyboard Controller
window.Stimulus.register("keyboard", class extends Controller {
    connect() {
        console.log('⌨️ Klavye kısayolları aktif:')
        console.log('   Ctrl+Enter: Hızlı görev ekleme')
        console.log('   Double-click: Görev düzenleme')
        console.log('   Escape: Düzenlemeyi iptal et')
        
        this.bindGlobalShortcuts()
    }
    
    disconnect() {
        this.unbindGlobalShortcuts()
    }
    
    bindGlobalShortcuts() {
        this.shortcutHandler = (event) => {
            // Ctrl+/ ile help göster
            if (event.ctrlKey && event.key === '/') {
                event.preventDefault()
                this.showHelp()
            }
            
            // ESC ile focus'u input'a al
            if (event.key === 'Escape') {
                const input = document.querySelector('[data-form-target="input"]')
                if (input) {
                    input.focus()
                }
            }
        }
        
        document.addEventListener('keydown', this.shortcutHandler)
    }
    
    unbindGlobalShortcuts() {
        if (this.shortcutHandler) {
            document.removeEventListener('keydown', this.shortcutHandler)
        }
    }
    
    showHelp() {
        const helpText = `🔥 Hotwire Todo - Klavye Kısayolları:

⌨️ Ctrl + Enter = Hızlı görev ekleme
🖱️ Double-click = Görev düzenleme  
⌨️ Enter = Düzenlemeyi kaydet
⌨️ Escape = Düzenlemeyi iptal et / Input'a odaklan
⌨️ Ctrl + / = Bu yardımı göster`
        
        alert(helpText)
    }
})

// Loading Controller
window.Stimulus.register("loading", class extends Controller {
    show() {
        this.element.classList.add('loading')
        console.log('⏳ Loading başladı')
    }
    
    hide() {
        this.element.classList.remove('loading')
        console.log('✅ Loading tamamlandı')
    }
})

// Global event listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log('🔥 Hotwire Todo App yüklendi!')
    console.log('💡 Ctrl+/ tuşuna basarak klavye kısayollarını öğrenebilirsin')
    
    // Input'a otomatik focus
    const input = document.querySelector('[data-form-target="input"]')
    if (input) {
        setTimeout(() => input.focus(), 100)
    }
})

// Hata yakalama
window.addEventListener('error', (event) => {
    console.error('❌ JavaScript hatası:', event.error)
})

// Basit Turbo polyfill (eğer yüklenmezse)
if (!window.Turbo) {
    console.warn('⚠️ Turbo yüklenemedi, basit polyfill kullanılıyor')
    
    // Basit form handling
    document.addEventListener('submit', (event) => {
        const form = event.target
        if (form.hasAttribute('data-turbo-frame')) {
            // AJAX form submission simulation
            console.log('📡 Form AJAX ile gönderiliyor')
        }
    })
    
    // Basit link handling  
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a')
        if (link && link.hasAttribute('data-turbo-frame')) {
            // AJAX link simulation
            console.log('📡 Link AJAX ile açılıyor')
        }
    })
}
