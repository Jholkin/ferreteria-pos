<template>
    <section>
        <nav class="level mt-2">
            <div class="level-left">
                <div class="level-item">
                    <p class="title is-1">Productos</p>
                </div>
            </div>
            <div class="level-right">
                <p class="level-item">
                    <b-button
                        class="is-rounded"
                        type="is-primary"
                        size="is-large"
                        icon-left="plus"
                        tag="router-link"
                        :to="{ path: '/agregar-producto' }">
                        Agregar producto
                    </b-button>
                </p>
                <p class="level-item">
                    <b-button
                        class="is-rounded"
                        type="is-info"
                        size="is-large"
                        icon-left="upload"
                        @click="mostrarModalImportar = true">
                        Importar existencia
                    </b-button>
                </p>
            </div>
        </nav>
        <b-breadcrumb
            align="is-left"
        >
            <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
            <b-breadcrumb-item active>Productos</b-breadcrumb-item>
        </b-breadcrumb>     
        <mensaje-inicial :titulo="'No se han encontrado productos :('" :subtitulo="'Agrega productos pulsando el botón de Agregar productos'" v-if="productos.length<1"/>
        
        <div v-if="productos.length>0">
            <!--cartas-totales :totales="cartasTotales" /-->
            <b-select v-model="perPage">
                <option value="5">5 por página</option>
                <option value="10">10 por página</option>
                <option value="15">15 por página</option>
                <option value="20">20 por página</option>
            </b-select>

            <b-table
            class="box"
            :data="productos"
            :paginated="isPaginated"
            :per-page="perPage"
            :current-page.sync="currentPage"
            :pagination-simple="isPaginationSimple"
            :pagination-position="paginationPosition"
            :default-sort-direction="defaultSortDirection"
            :pagination-rounded="isPaginationRounded"
            :sort-icon="sortIcon"
            :sort-icon-size="sortIconSize"
            default-sort="user.first_name"
            aria-next-label="Next page"
            aria-previous-label="Previous page"
            aria-page-label="Page"
            aria-current-label="Current page"
            >
              <b-table-column field="codigo" label="Código" sortable searchable v-slot="props">
                    {{ props.row.codigo }}
                </b-table-column>

                <b-table-column field="nombre" label="Nombre" sortable searchable v-slot="props">
                    {{ props.row.nombre }}
                </b-table-column>

                <b-table-column field="precioCompra" label="Precio compra" sortable v-slot="props">
                    ${{ props.row.precioCompra }}
                </b-table-column>

                <b-table-column field="precioVenta" label="Precio venta" sortable v-slot="props">
                    ${{ props.row.precioVenta }}
                </b-table-column>

                <b-table-column field="ganancia" label="Ganacia" sortable v-slot="props">
                    <b>${{ props.row.precioVenta - props.row.precioCompra }}</b>
                </b-table-column>

                <b-table-column field="vendidoMayoreo" label="¿Mayoreo?" sortable v-slot="props">
                    <b-tag type="is-danger" v-if="!props.row.vendidoMayoreo">No</b-tag>

                    <div v-if="props.row.vendidoMayoreo">
                        <b>Precio: </b>${{ props.row.precioMayoreo}}<br>
                        <b>A partir: </b>{{ props.row.cantidadMayoreo}}
                    </div>
                </b-table-column>

                <b-table-column field="existencia" label="Existencia" sortable v-slot="props">
                    {{ props.row.existencia }}
                </b-table-column>

                <b-table-column field="nombreMarca" label="Marca" sortable searchable v-slot="props">
                    {{ props.row.nombreMarca }}
                </b-table-column>

                <b-table-column field="nombreCategoria" label="Categoría" sortable searchable v-slot="props">
                    {{ props.row.nombreCategoria }}
                </b-table-column>

                <b-table-column field="eliminar" label="Eliminar" v-slot="props">
                    <b-button type="is-danger" @click="eliminar(props.row.id)">
                        <b-icon icon="delete" />
                    </b-button>
                </b-table-column>

                <b-table-column field="editar" label="Editar" v-slot="props">
                    <b-button type="is-info" @click="editar(props.row.id)">
                        <b-icon icon="pen" />
                    </b-button>
                </b-table-column>

                <b-table-column field="editar" label="Agregar" v-slot="props">
                    <b-button type="is-primary" @click="agregarExistencia(props.row)">
                        <b-icon icon="plus" />
                    </b-button>
                </b-table-column>

                <b-table-column field="editar" label="Quitar" v-slot="props">
                    <b-button type="is-warning" @click="quitarExistencia(props.row)">
                        <b-icon icon="minus" />
                    </b-button>
                </b-table-column>
            </b-table>
        </div>

        <b-modal
            v-model="mostrarModalImportar"
            has-modal-card
            trap-focus
            :destroy-on-hide="true"
            aria-role="dialog"
            aria-label="Modal Importar Existencia"
            close-button-aria-label="Close"
            aria-modal>
            <div class="modal-card" style="width: 450px">
                <header class="modal-card-head">
                    <p class="modal-card-title">Importar existencias desde Excel</p>
                    <button type="button" class="delete" @click="cerrarModalImportar"/>
                </header>
                <section class="modal-card-body">
                    <b-field>
                        <b-button
                            type="is-success"
                            icon-left="download"
                            @click="descargarPlantilla"
                        >
                            1. Descargar plantilla Excel
                        </b-button>
                    </b-field>
                    <b-field label="Selecciona archivo Excel (.xlsx)">
                        <b-upload
                            accept=".xlsx"
                            @input="onArchivoSeleccionado"
                            v-model="archivoExcel"
                        >
                            <a class="button is-info">
                                <b-icon icon="upload"></b-icon>
                                <span>2. Seleccionar archivo</span>
                            </a>
                        </b-upload>
                    </b-field>
                    <b-field v-if="nombreArchivoExcel">
                        <b-tag type="is-info">{{ nombreArchivoExcel }}</b-tag>
                    </b-field>
                    <b-field>
                        <b-button
                            type="is-primary"
                            :disabled="!archivoExcel"
                            @click="leerArchivoExcel"
                        >
                            3. Procesar
                        </b-button>
                    </b-field>
                </section>
            </div>
        </b-modal>

        <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
    </section>
</template>
<script>
    import * as XLSX from 'xlsx'
    import HttpService from '../../Servicios/HttpService'
    import NavComponent from '../Extras/NavComponent'
    import MensajeInicial from '../Extras/MensajeInicial'
    import CartasTotales from '../Extras/CartasTotales'
    import AyudanteSesion from '../../Servicios/AyudanteSesion'

    export default {
        name: "ProductComponent",
        components: { NavComponent, MensajeInicial, CartasTotales },

        data: ()=>({
            productos: [],
            cargando: false,
            isPaginated: true,
            isPaginationSimple: false,
            isPaginationRounded: true,
            paginationPosition: 'bottom',
            defaultSortDirection: 'asc',
            sortIcon: 'arrow-up',
            sortIconSize: 'is-medium',
            currentPage: 1,
            perPage: 5,
            cartasTotales: [],
            mostrarModalImportar: false,
            archivoExcel: null
        }),

        mounted(){
            this.obtenerProductos()
        },

        methods: {
            agregarExistencia(producto){
               this.$buefy.dialog.prompt({
                   message: '¿Cuántas piezas vas a agregar de ' + producto.nombre + '?',
                   cancelText: 'Cancelar',
                   confirmText: 'Agregar',
                   inputAttrs: {
                       type: 'number',
                       placeholder: 'Escribe la cantidad de productos',
                       value: '',
                       min: 1
                   },
                   trapFocus: true,
                   onConfirm: (value) =>{ 
                     this.cargando = true
                     HttpService.registrar('productos.php', {
                        accion: 'agregar_existencia',
                        cantidad: value,
                        id: producto.id,
                        idUsuario: AyudanteSesion.obtenerDatosSesion().id
                     })
                     .then(registrado => {
                        if(registrado){
                           this.cargando = false
                           this.$buefy.toast.open(value + ' Productos agregados a ' + producto.nombre)
                           this.obtenerProductos()
                        }
                     })
                    
                  }
               })
            },

            quitarExistencia(producto){
               this.$buefy.dialog.prompt({
                   message: '¿Cuántas piezas vas a quitar de ' + producto.nombre + '?',
                   cancelText: 'Cancelar',
                   confirmText: 'Quitar',
                   inputAttrs: {
                       type: 'number',
                       placeholder: 'Escribe la cantidad de productos',
                       value: '',
                       min: 1
                   },
                   trapFocus: true,
                   onConfirm: (value) =>{ 
                     if(value > producto.existencia){
                        this.$buefy.toast.open('No puedes quitar más de ' + producto.existencia + ' productos')
                        return
                     }
                     this.cargando = true
                     HttpService.registrar('productos.php', {
                        accion: 'restar_existencia',
                        cantidad: value,
                        id: producto.id,
                        idUsuario: AyudanteSesion.obtenerDatosSesion().id
                     })
                     .then(registrado => {
                        if(registrado){
                           this.cargando = false
                           this.$buefy.toast.open(value + ' Productos quitados a ' + producto.nombre)
                           this.obtenerProductos()
                        }
                     })
                    
                  }
               })
            },

            async eliminar(idProducto){
                this.$buefy.dialog.confirm({
                    title: 'Eliminar producto',
                    message: 'Seguro que quieres <b>eliminar</b> este producto? Esta acción no se puede revertir.',
                    confirmText: 'Sí, eliminar',
                    cancelText: 'Cancelar',
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: () => {
                        this.cargando = true
                        HttpService.eliminar('productos.php',{
                            accion: 'eliminar',
                            id: idProducto
                        })
                        .then(resultado => {
                            if(!resultado) {
                                this.$buefy.toast.open('Error al eliminar')
                                this.cargando = false
                                return
                            }

                            if(resultado){
                                this.cargando = false
                                this.$buefy.toast.open({
                                    type: 'is-info',
                                    message: 'Producto eliminado.'
                                })
                                this.obtenerProductos()
                            }
                        })
                    }
                })
            },

            editar(idProducto){
                this.$router.push({
                    name: "EditarProducto",
                    params: { id: idProducto}
                })
            },

            obtenerProductos(){
                this.cargando = true
                let payload = {
                    accion: 'obtener'
                }
                HttpService.obtenerConConsultas('productos.php', payload)
                .then(respuesta => {
                    this.productos = respuesta.productos

                    this.cartasTotales = [
                        {nombre: "Número Productos", total: this.productos.length, icono: "package-variant-closed", clase: "has-text-danger"},
                        {nombre: "Total productos", total: respuesta.totalProductos, icono: "chart-bar-stacked", clase: "has-text-primary"},
                        {nombre: "Total inventario", total: '$' + respuesta.totalInventario, icono: "currency-usd", clase: "has-text-success"},
                        {nombre: "Ganancia", total: '$' + respuesta.gananciaInventario, icono: "currency-usd", clase: "has-text-info"},
                    ]
                    this.cargando = false
                })
            },

            cerrarModalImportar() {
                this.mostrarModalImportar = false
                this.archivoExcel = null
                this.nombreArchivoExcel = ""
            },

            onArchivoSeleccionado(file) {
                this.archivoExcel = file
                this.nombreArchivoExcel = file ? file.name : ""
            },

            async descargarPlantilla() {
                this.cargando = true
                try {
                    const payload = { accion: 'obtener' }
                    const respuesta = await HttpService.obtenerConConsultas('productos.php', payload)
                    const datos = (respuesta.productos || []).map(p => ({
                        id: p.id,
                        codigo: p.codigo,
                        nombre: p.nombre,
                        existencia: p.existencia
                    }))
                    const ws = XLSX.utils.json_to_sheet(datos)
                    const wb = XLSX.utils.book_new()
                    XLSX.utils.book_append_sheet(wb, ws, "Productos")
                    XLSX.writeFile(wb, "plantilla_existencias.xlsx")
                } catch (e) {
                    this.$buefy.toast.open({
                        type: 'is-danger',
                        message: 'No se pudo descargar la plantilla.'
                    })
                }
                this.cargando = false
            },

            async leerArchivoExcel() {
                if (!this.archivoExcel) return
                this.cargando = true
                try {
                    const reader = new FileReader()
                    reader.onload = async (e) => {
                        const data = new Uint8Array(e.target.result)
                        const workbook = XLSX.read(data, { type: 'array' })
                        const sheetName = workbook.SheetNames[0]
                        const worksheet = workbook.Sheets[sheetName]
                        const registros = XLSX.utils.sheet_to_json(worksheet)
                        let errores = []
                        let actualizados = 0
                        for (const [i, reg] of registros.entries()) {
                            const id = reg.id ? String(reg.id).trim() : ""
                            const codigo = reg.codigo ? String(reg.codigo).trim() : ""
                            const cantidad = reg.existencia !== undefined ? reg.existencia : ""
                            if (!id || cantidad === "" || cantidad === null) {
                                errores.push(`Fila ${i + 2}: Código y existencia son obligatorios.`)
                                continue
                            }
                            const resp = await HttpService.registrar('productos.php', {
                                accion: 'agregar_existencia',
                                id,
                                cantidad,
                                idUsuario: AyudanteSesion.obtenerDatosSesion().id
                            })
                            if (resp) {
                                actualizados++
                            } else {
                                errores.push(`Fila ${i + 2}: No se pudo actualizar el producto con código ${codigo}.`)
                            }
                        }
                        this.$buefy.toast.open({
                            type: errores.length ? 'is-warning' : 'is-success',
                            message: errores.length
                                ? `Importación finalizada con errores:<br>${errores.join('<br>')}`
                                : `Existencias actualizadas correctamente (${actualizados} productos).`,
                            duration: 8000
                        })
                        this.obtenerProductos()
                        this.cerrarModalImportar()
                    }
                    reader.readAsArrayBuffer(this.archivoExcel)
                } catch (e) {
                    this.$buefy.toast.open({
                        type: 'is-danger',
                        message: 'Error al procesar el archivo Excel.'
                    })
                    this.cargando = false
                }
            }
        }
    }
</script>