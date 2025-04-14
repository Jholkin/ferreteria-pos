<template>
    <form action="">
        <!-- Modal para gestionar la categoría -->
        <div class="modal-card" style="width: 600px">
            <header class="modal-card-head">
                <!-- Título del modal, accede a la prop "titulo" para mostrar el nombre de la categoría -->
                <p class="modal-card-title">{{ titulo }} categoría</p>
                <!-- Botón para cerrar el modal, emite un evento 'close' cuando se hace clic -->
                <button type="button" class="delete" @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <!-- Campo para ingresar el nombre de la categoría -->
                <b-field label="Nombre de la categoría">
                    <b-input type="text" placeholder="Ej. Herramientas" v-model="nombreCategoria"></b-input>
                </b-field>
            </section>
            <footer class="modal-card-foot">
                <!-- Botón de cancelar, emite un evento 'close' al hacer clic -->
                <b-button label="Cancelar" icon-left="cancel" size="is-medium" @click="$emit('close')" />
                <!-- Botón para registrar la categoría, ejecuta el método 'registrar' -->
                <b-button label="Registrar" type="is-primary" icon-left="check" @click="registrar" />
            </footer>
        </div>
    </form>
</template>

<script>
export default {
    // Nombre del componente
    name: "DialogoCategorias",
    
    // Propiedades que recibe el componente (encapsula los datos pasados desde el componente padre)
    props: ['titulo', 'nombre'],

    data: () => ({
        // Estado local del componente, el nombre de la categoría será manipulado aquí
        nombreCategoria: "",
    }),

    // Cuando el componente es montado, se inicializa el campo 'nombreCategoria' con la prop 'nombre'
    mounted() {
        this.nombreCategoria = this.nombre;
    },

    methods: {
        // Método para registrar la categoría
        registrar() {
            // Validación: si el nombre de la categoría está vacío, muestra un mensaje de error
            if (!this.nombreCategoria) {
                this.$buefy.toast.open({
                    type: 'is-danger',
                    message: 'Debes colocar el nombre de la categoría.'
                });
                return;  // Si no pasa la validación, no se ejecuta el resto del código
            }

            // Emite el evento 'registrar' con el valor de 'nombreCategoria' hacia el componente padre
            this.$emit("registrar", this.nombreCategoria);
        }
    }
}
</script>
