// Minimal setup
import 'megio-panel/styles'
import { createMegioPanel } from 'megio-panel'

createMegioPanel(window.location.host.includes('localhost') ? 'http://localhost:8090/' : '/')

// Advanced setup
// https://github.com/strategio-digital/megio-panel