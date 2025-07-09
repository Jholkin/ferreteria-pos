<template>
	<section>
		<!--b-field label="Nombre del cliente">
			<b-autocomplete
				v-model="cliente"
				id="cliente"
				placeholder="Escribe el nombre del cliente"
				:keep-first="true"
				:data="clientesFiltrados"
				field="nombre"
				@input="buscarClientes"
				@select="seleccionarCliente"
				size="is-medium"
			>
			</b-autocomplete>
		</b-field-->
		<b-field label="DNI del cliente">
			<b-autocomplete
				v-model="cliente.dni"
				id="dni"
				placeholder="Escribe el dni del cliente"
				:keep-first="true"
				:data="clientesFiltrados"
				field="dni"
				@keyup.enter.native="buscarClientePorDNI"
				@select="seleccionarCliente"
				size="is-medium"
			>
				<template #default="props">
					<span>{{ props.option.dni }} | {{ props.option.nombre }}</span>
				</template>
			</b-autocomplete>
		</b-field>
		<b-field label="Teléfono del cliente">
			<b-input
				v-model="cliente.telefono"
				id="telefono"
				placeholder="Escribe el teléfono del cliente"
				size="is-medium"
				@blur="addTelefonoAClienteSeleccionado"
			>
			</b-input>
		</b-field>
		<div class="notification is-info mt-2" v-if="clienteSeleccionado">
			<button class="delete" @click="deseleccionarCliente"></button>
			<p>Cliente: <b>{{ clienteSeleccionado.nombre }}</b></p>
			<p>DNI: <b>{{ clienteSeleccionado.dni }}</b></p>
			<!--p>Teléfono: <b>{{ clienteSeleccionado.telefono }}</b></p-->
		</div>
	</section>
</template>
<script>
	import HttpService from '../../Servicios/HttpService'

	export default{
		name: "BusquedaCliente",

		data:()=>({
			cliente: {
				nombre: '',
				dni: '',
				telefono: ''
			},
			clientesEncontrados: [],
			clienteSeleccionado: null
		}),

		methods: {
			deseleccionarCliente(){
				this.clienteSeleccionado = null
			},
			seleccionarCliente(opcion) {
				if(!opcion) return
				this.clienteSeleccionado = opcion
				this.$emit("seleccionado", this.clienteSeleccionado)
				setTimeout(() => this.cliente.dni = '', 10)
			},

			buscarClientes(){
				let payload = {
					accion: 'obtener_por_nombre',
					nombre: this.cliente
				}

				HttpService.obtenerConConsultas('clientes.php', payload)
				.then(clientes =>{ 
					this.clientesEncontrados = clientes
				})
			},
			buscarClientePorDNI() {
				if (!this.cliente.dni) return;
				if (this.cliente.dni.length < 8 || this.cliente.dni.length > 8) {
					this.$buefy.toast.open({
						type: 'is-danger',
						message: 'El DNI debe tener al menos 8 caracteres.'
					})
					return;
				}
				HttpService.obtenerConConsultas("clientes.php", {
                    accion: "obtener_por_dni",
                    dni: this.cliente.dni
                })
                .then(cliente =>{
                    let {data} = cliente
					this.clientesEncontrados = [{
						nombre: data.nombreCompleto,
						dni: data.numeroDocumento,
						telefono: this.esTelefonoValido(this.cliente.telefono) ? this.cliente.telefono : ''
					}]
					console.log(this.clientesEncontrados);
                })	
			},
			addTelefonoAClienteSeleccionado() {
				if (!this.esTelefonoValido(this.cliente.telefono)) {
					this.$buefy.toast.open({
						type: 'is-danger',
						message: 'El número de celular debe empezar con 9 y tener 9 dígitos.'
					});
					return;
				}
				if (!this.clienteSeleccionado) return;
				this.clienteSeleccionado.telefono = this.cliente.telefono;
				this.clienteSeleccionado.isValidTelephone = true;
				this.$emit("seleccionado", this.clienteSeleccionado);
			},
			esTelefonoValido(telefono) {
				if (!telefono) return false;
				const regexCelularPeru = /^9\d{8}$/;
				return regexCelularPeru.test(telefono);
			}
		},

		computed: {
			clientesFiltrados() {
				return this.clientesEncontrados.filter(opcion => {
					return (
						opcion.dni
							.toString()
							.toLowerCase()
							.indexOf(this.cliente.dni.toLowerCase()) >= 0
					)
				})
			}
		}

	}
</script>