<template>
    <section>
        <!--nav-component :titulo="'Inventario'" :link="{ path: '/agregar-producto' }" :texto="'Agregar producto'"/-->
        <b-breadcrumb
            align="is-left"
        >
            <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
            <b-breadcrumb-item active>Inventario</b-breadcrumb-item>
        </b-breadcrumb>     
        <mensaje-inicial :titulo="'No se han encontrado productos :('" :subtitulo="'Agrega productos pulsando el botón de Agregar productos'" v-if="productos.length<1"/>
        
        <div v-if="productos.length>0">
            <cartas-totales :totales="cartasTotales" />
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

                <b-table-column field="kardex" label="Movimientos" v-slot="props">
                    <b-button type="is-info" @click="abrirKardex(props.row)">
                        <b-icon icon="clipboard-list-outline" />
                    </b-button>
                </b-table-column>

                <b-modal
                    v-model="mostrarModalKardex"
                    has-modal-card
                    trap-focus
                    :destroy-on-hide="true"
                    aria-role="dialog"
                    aria-label="Kardex de producto"
                    close-button-aria-label="Close"
                    aria-modal>
                    <div class="modal-card" style="width: 900px; max-width: 98vw;">
                        <header class="modal-card-head">
                            <p class="modal-card-title">
                                Kardex de producto: <b>{{ kardexProductoSeleccionado.nombre }}</b> ({{ kardexProductoSeleccionado.codigo }})
                            </p>
                            <button type="button" class="delete" @click="cerrarModalKardex"/>
                        </header>
                        <section class="modal-card-body">
                            <b-field grouped>
                                <b-field label="Desde">
                                    <b-datepicker v-model="filtroKardex.desde" placeholder="Desde" icon="calendar-today"/>
                                </b-field>
                                <b-field label="Hasta">
                                    <b-datepicker v-model="filtroKardex.hasta" placeholder="Hasta" icon="calendar-today"/>
                                </b-field>
                                <b-field label="Movimiento">
                                    <b-select v-model="filtroKardex.tipo" placeholder="Todos">
                                        <option value="">Todos</option>
                                        <option value="ingreso">Ingreso</option>
                                        <option value="salida">Salida</option>
                                        <option value="venta">Venta</option>
                                        <option value="apartado">Apartado</option>
                                    </b-select>
                                </b-field>
                                <b-button type="is-primary" @click="filtrarKardex">Filtrar</b-button>
                            </b-field>
                            <b-table
                                :data="movimientosKardex"
                                :striped="true"
                                :hoverable="true"
                                :narrowed="true"
                                :mobile-cards="true"
                            >
                                <b-table-column field="fecha" label="Fecha" v-slot="props">
                                    {{ props.row.fecha }}
                                </b-table-column>
                                <b-table-column field="movimiento" label="Movimiento" v-slot="props">
                                    <b-tag :type="colorMovimiento(props.row.tipo)">
                                        {{ props.row.tipo | capitalize }}
                                    </b-tag>
                                </b-table-column>
                                <b-table-column field="documento" label="Documento" v-slot="props">
                                    {{ props.row.documento || '-' }}
                                </b-table-column>
                                <b-table-column field="cantidad" label="Cantidad" v-slot="props">
                                    <span :class="{'has-text-success': props.row.tipo === 'ingreso', 'has-text-danger': props.row.tipo !== 'ingreso'}">
                                        {{ props.row.cantidad }}
                                    </span>
                                </b-table-column>
                                <b-table-column field="existencia" label="Existencia" v-slot="props">
                                    {{ props.row.existencia }}
                                </b-table-column>
                                <b-table-column field="usuario" label="Usuario" v-slot="props">
                                    {{ props.row.usuario }}
                                </b-table-column>
                                <b-table-column field="observacion" label="Observación" v-slot="props">
                                    {{ props.row.observacion || '-' }}
                                </b-table-column>
                            </b-table>
                            <div class="mt-4">
                                <b-message type="is-info" title="Totales del Kardex" size="is-small">
                                    <div class="columns is-multiline">
                                        <div class="column is-3"><b>Ingresos:</b> {{ totalIngresos }}</div>
                                        <div class="column is-3"><b>Salidas:</b> {{ totalSalidas }}</div>
                                        <div class="column is-3"><b>Ventas:</b> {{ totalVentas }}</div>
                                        <div class="column is-3"><b>Existencia actual:</b> {{ existenciaActual }}</div>
                                    </div>
                                </b-message>
                            </div>
                        </section>
                    </div>
                </b-modal>
            </b-table>
        </div>
        <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
    </section>
</template>
<script>
    import HttpService from '../../Servicios/HttpService'
    import NavComponent from '../Extras/NavComponent'
    import MensajeInicial from '../Extras/MensajeInicial'
    import CartasTotales from '../Extras/CartasTotales'

    export default {
        name: "ProductosComponent",
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
            // kardex data
            mostrarModalKardex: false,
            kardexProductoSeleccionado: {},
            movimientosKardex: [],
            filtroKardex: {
                desde: null,
                hasta: null,
                tipo: ""
            },
            totalIngresos: 0,
            totalSalidas: 0,
            totalVentas: 0,
            existenciaActual: 0
        }),

        mounted(){
            this.obtenerProductos()
        },

        methods: {
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
            abrirKardex(producto) {
                this.kardexProductoSeleccionado = producto
                this.filtroKardex = { desde: null, hasta: null, tipo: "" }
                this.obtenerKardex()
                this.mostrarModalKardex = true
            },
            cerrarModalKardex() {
                this.mostrarModalKardex = false
                this.movimientosKardex = []
            },
            async obtenerKardex() {
                this.cargando = true
                const payload = {
                    accion: 'obtener_kardex',
                    id: this.kardexProductoSeleccionado.id,
                    desde: this.filtroKardex.desde,
                    hasta: this.filtroKardex.hasta,
                    tipo: this.filtroKardex.tipo
                }
                const respuesta = await HttpService.obtenerConConsultas('productos.php', payload)
                this.movimientosKardex = respuesta.movimientos || []
                this.totalIngresos = respuesta.totalIngresos || 0
                this.totalSalidas = respuesta.totalSalidas || 0
                this.totalVentas = respuesta.totalVentas || 0
                this.existenciaActual = respuesta.existenciaActual || 0
                this.cargando = false
            },
            filtrarKardex() {
                this.obtenerKardex()
            },
            colorMovimiento(tipo) {
                if (tipo === 'ingreso') return 'is-success'
                if (tipo === 'salida') return 'is-danger'
                if (tipo === 'venta') return 'is-warning'
                return 'is-dark'
            }
        },
        filters: {
            capitalize(val) {
                if (!val) return ''
                return val.charAt(0).toUpperCase() + val.slice(1)
            }
        }
    }
</script>