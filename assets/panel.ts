// Minimal setup
import 'megio-panel/styles'
import { createMegioPanel } from 'megio-panel'

const apiUrl = window.location.host.includes('localhost') ? 'http://localhost:8090/' : '/'
createMegioPanel(apiUrl)

// Advanced setup
// https://github.com/strategio-digital/megio-panel