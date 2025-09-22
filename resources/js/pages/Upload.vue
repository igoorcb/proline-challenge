<template>
  <div class="px-4 py-6">
    <div class="max-w-2xl mx-auto">
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Upload de Arquivo JSON</h2>
          <p class="mt-1 text-sm text-gray-500">
            Selecione um arquivo .json para processamento em background
          </p>
        </div>

        <div class="px-6 py-4">
          <div v-if="!uploadResult" class="space-y-4">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
              <input
                ref="fileInput"
                type="file"
                accept=".json"
                @change="handleFileSelect"
                class="hidden"
              >

              <div v-if="!selectedFile">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                  <button @click="$refs.fileInput.click()" class="font-medium text-blue-600 hover:text-blue-500">
                    Clique para selecionar
                  </button>
                  ou arraste um arquivo .json aqui
                </p>
              </div>

              <div v-else class="text-sm">
                <p class="text-gray-900 font-medium">{{ selectedFile.name }}</p>
                <p class="text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                <button @click="clearFile" class="mt-2 text-red-600 hover:text-red-500">
                  Remover arquivo
                </button>
              </div>
            </div>

            <div class="flex justify-end">
              <button
                @click="uploadFile"
                :disabled="!selectedFile || uploading"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="uploading">Enviando...</span>
                <span v-else>Enviar Arquivo</span>
              </button>
            </div>
          </div>

          <div v-else class="text-center space-y-4">
            <div class="text-green-600">
              <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-medium text-gray-900">Upload Realizado com Sucesso!</h3>
              <p class="text-sm text-gray-500 mt-1">
                ID do lote: <span class="font-medium">{{ uploadResult.batch_id }}</span>
              </p>
              <p class="text-sm text-gray-500">
                Status: <span class="font-medium">{{ uploadResult.status }}</span>
              </p>
            </div>
            <div class="flex justify-center space-x-4">
              <router-link
                :to="'/app/status/' + uploadResult.batch_id"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
              >
                Ver Status
              </router-link>
              <button
                @click="resetUpload"
                class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
              >
                Novo Upload
              </button>
            </div>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mt-4">
            <div class="flex">
              <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Erro no upload</h3>
                <p class="text-sm text-red-700 mt-1">{{ error }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Upload',
  data() {
    return {
      selectedFile: null,
      uploading: false,
      uploadResult: null,
      error: null
    }
  },
  methods: {
    handleFileSelect(event) {
      const file = event.target.files[0];
      if (file && file.type === 'application/json') {
        this.selectedFile = file;
        this.error = null;
      } else {
        this.error = 'Por favor, selecione um arquivo .json válido';
      }
    },

    clearFile() {
      this.selectedFile = null;
      this.$refs.fileInput.value = '';
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    async uploadFile() {
      if (!this.selectedFile) return;

      this.uploading = true;
      this.error = null;

      try {
        const formData = new FormData();
        formData.append('file', this.selectedFile);

        const response = await axios.post('/api/uploads', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        this.uploadResult = response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Erro ao enviar arquivo';
      } finally {
        this.uploading = false;
      }
    },

    resetUpload() {
      this.selectedFile = null;
      this.uploadResult = null;
      this.error = null;
      this.$refs.fileInput.value = '';
    }
  }
}
</script>