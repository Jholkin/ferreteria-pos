<template>
    <div class="modal-card" style="width: 400px">
        <header class="modal-card-head">
            <p class="modal-card-title">Opciones del comprobante</p>
            <button
                type="button"
                class="delete"
                @click="$emit('close')"/>
        </header>
        <section class="modal-card-body">
            <div class="buttons is-centered">
                <b-button
                    type="is-info"
                    icon-left="printer"
                    size="is-medium"
                    @click="imprimir">
                    Imprimir comprobante
                </b-button>
                <b-button
                    type="is-success"
                    icon-left="whatsapp"
                    size="is-medium"
                    @click="enviarWhatsApp">
                    Enviar por WhatsApp
                </b-button>
                <b-button
                    type="is-warning"
                    icon-left="email"
                    size="is-medium"
                    @click="mostrarInputCorreo = true">
                    Enviar por correo
                </b-button>
            </div>
            <div v-if="mostrarInputCorreo" class="mt-4">
                <b-field label="Correo electrónico del cliente" :type="emailError ? 'is-danger' : ''" :message="emailError">
                    <b-input
                        v-model="correoCliente"
                        placeholder="ejemplo@correo.com"
                        type="email"
                        @keyup.enter="enviarCorreo"
                        @blur="validarCorreo"
                        icon="email"
                        icon-pack="mdi"
                        />
                </b-field>
                <div class="buttons is-right">
                    <b-button type="is-primary" @click="enviarCorreo">Enviar</b-button>
                    <b-button @click="cancelarCorreo">Cancelar</b-button>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import AyudanteSesion from '../../Servicios/AyudanteSesion'
import HttpService from '../../Servicios/HttpService'

export default {
    name: "DialogoOpcionesComprobante",
    props: ['venta'],

    data:()=>({
        titulo: "",
        datosNegocio: null,
        mostrarInputCorreo: false,
        correoCliente: "",
        emailError: ""
    }),

    beforeMount(){
        this.generarTitulo()
        this.obtenerDatosNegocio()
    },
    
    methods: {
        imprimir() {
            this.$emit('imprimir')
        },

        generarTitulo(tipo){
            switch(tipo){
                case "venta":
                    this.titulo = "COMPROBANTE DE COMPRA"
                    break
                case "cuenta":
                    this.titulo = "COMPROBANTE DE CUENTA"
                    break

                case "apartado":
                    this.titulo = "COMPROBANTE DE APARTADO"
                    break

                case "cotiza":
                    this.titulo = "COTIZACIÓN"
                    break

                default:
                    this.titulo = "COMPROBANTE"
            }
        },

        obtenerDatosNegocio(){
            this.datosNegocio = AyudanteSesion.obtenerDatosNegocio()
        },
        
        enviarWhatsApp() {
            this.generarTitulo(this.venta.tipo)
            this.obtenerDatosNegocio()
            let mensaje = this.formatearMensajeComprobante()
            let numero = this.venta.contactoCliente.replace(/\D/g, '')
            let url = `https://web.whatsapp.com/send?phone=51${numero}&text=${encodeURIComponent(mensaje)}`
            window.open(url, '_blank')
            this.$emit('close')
        },

        formatearMensajeComprobante() {
            let mensaje = `*${this.titulo}*\n`
            mensaje += `*${this.datosNegocio.nombre}*\n`
            mensaje += `Telefono: ${this.datosNegocio.telefono}\n\n`
            mensaje += `Cliente: ${this.venta.nombreCliente}\n`
            mensaje += `Atiende: ${this.venta.nombreUsuario}\n`
            mensaje += `Fecha: ${this.venta.fecha}\n\n`
            mensaje += `*Productos:*\n`
            
            this.venta.productos.forEach(producto => {
                mensaje += `${producto.nombre}\n`
                mensaje += `$${producto.precio} X ${producto.cantidad}\n`
                mensaje += `Subtotal: $${producto.precio * producto.cantidad}\n\n`
            })
            
            mensaje += `*Total:* $${this.venta.total}\n`
            
            if (this.venta.tipo !== 'cotiza') {
                mensaje += `*Pago:* $${this.venta.pagado}\n`
            }
            
            if (this.venta.tipo === 'venta') {
                mensaje += `*Cambio:* $${this.venta.pagado - this.venta.total}\n`
            }
            
            if (this.venta.tipo === 'cuenta' || this.venta.tipo === 'apartado') {
                mensaje += `*Por pagar:* $${this.venta.porPagar}\n`
            }
            
            mensaje += `\nGracias por su preferencia`
            
            return mensaje
        },
        validarCorreo() {
            // Expresión regular básica para validar correo electrónico
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            if (!this.correoCliente) {
                this.emailError = "El correo es obligatorio."
                return false
            }
            if (!regex.test(this.correoCliente)) {
                this.emailError = "Ingrese un correo válido."
                return false
            }
            this.emailError = ""
            return true
        },

        cancelarCorreo() {
            this.mostrarInputCorreo = false
            this.correoCliente = ""
            this.emailError = ""
        },

        enviarCorreo() {
            if (!this.validarCorreo()) return
            this.generarTitulo(this.venta.tipo)
            this.obtenerDatosNegocio()
            const mensaje = this.formatearMensajeComprobante().replace(/\n/g, "<br>")
            const datosCorreo = {
                to: this.correoCliente,
                toName: this.venta.nombreCliente || "Cliente",
                subject: this.titulo,
                body: mensaje,
                from: this.datosNegocio.correo || "no-reply@tusistema.com",
                fromName: this.datosNegocio.nombre || "Negocio"
            }
            HttpService.registrar('vender.php', {
                accion: 'enviar_comprobante',
                datos: datosCorreo
            }).then(respuesta => {
                console.log(respuesta);
                if (respuesta && respuesta.success) {
                    this.$buefy.toast.open({
                        type: 'is-success',
                        message: 'Comprobante enviado correctamente al correo.'
                    })
                    this.cancelarCorreo()
                    this.$emit('close')
                } else {
                    this.$buefy.toast.open({
                        type: 'is-danger',
                        message: respuesta && respuesta.message ? respuesta.message : 'No se pudo enviar el correo.'
                    })
                }
            }).catch(error => {
                console.error(error);
                this.$buefy.toast.open({
                    type: 'is-danger',
                    message: 'Error al enviar el correo.'
                })
            });
        }
    }
}
</script> 