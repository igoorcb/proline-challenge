<template>
  <div class="px-4 py-6">
    <div class="max-w-6xl mx-auto">
      <div v-if="!$route.params.id">
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
              <h2 class="text-lg font-medium text-gray-900">Status dos Uploads</h2>
              <p class="mt-1 text-sm text-gray-500">
                Acompanhe o progresso dos seus uploads
              </p>
            </div>
            <button
              @click="loadUploads"
              class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
            >
              Atualizar
            </button>
          </div>

          <div class="px-6 py-4">
            <div v-if="loading" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <p class="mt-2 text-sm text-gray-500">Carregando...</p>
            </div>

            <div v-else-if="uploads.length === 0" class="text-center py-8">
              <p class="text-gray-500">Nenhum upload encontrado</p>
            </div>

            <div v-else class="overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itens</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="upload in uploads" :key="upload.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      #{{ upload.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusClass(upload.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                        {{ getStatusText(upload.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ upload.processed_items || 0 }} / {{ upload.total_items || 0 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(upload.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                      <router-link
                        :to="'/app/status/' + upload.id"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Ver Detalhes
                      </router-link>
                      <button
                        v-if="upload.status === 'error'"
                        @click="reprocessBatch(upload.id)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Reprocessar
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div v-else>
        <div class="mb-4">
          <router-link to="/app/status" class="text-blue-600 hover:text-blue-800">
            ← Voltar para lista
          </router-link>
        </div>

        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
              <h2 class="text-lg font-medium text-gray-900">
                Upload #{{ $route.params.id }}
              </h2>
              <p class="mt-1 text-sm text-gray-500">
                Detalhes do processamento
              </p>
            </div>
            <div class="flex space-x-2">
              <button
                @click="loadBatchDetail"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
              >
                Atualizar
              </button>
              <button
                v-if="batchDetail && batchDetail.status === 'error'"
                @click="reprocessBatch($route.params.id)"
                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
              >
                Reprocessar
              </button>
            </div>
          </div>

          <div class="px-6 py-4">
            <div v-if="loadingDetail" class="text-center py-8">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <p class="mt-2 text-sm text-gray-500">Carregando detalhes...</p>
            </div>

            <div v-else-if="batchDetail" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-gray-900">
                    <span :class="getStatusClass(batchDetail.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                      {{ getStatusText(batchDetail.status) }}
                    </span>
                  </div>
                  <div class="text-sm text-gray-500 mt-1">Status</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-gray-900">{{ batchDetail.total_items || 0 }}</div>
                  <div class="text-sm text-gray-500">Total de Itens</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-green-600">{{ batchDetail.processed_items || 0 }}</div>
                  <div class="text-sm text-gray-500">Processados</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-red-600">{{ batchDetail.failed_items || 0 }}</div>
                  <div class="text-sm text-gray-500">Falharam</div>
                </div>
              </div>

              <div v-if="batchDetail.statistics">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas por Tipo</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                  <div
                    v-for="(count, type) in batchDetail.statistics"
                    :key="type"
                    class="bg-blue-50 rounded-lg p-4"
                  >
                    <div class="text-xl font-bold text-blue-900">{{ count }}</div>
                    <div class="text-sm text-blue-700">{{ formatTypeName(type) }}</div>
                  </div>
                </div>
              </div>

              <div v-if="batchDetail.error_message" class="bg-red-50 border border-red-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-red-800">Erro</h3>
                <p class="text-sm text-red-700 mt-1">{{ batchDetail.error_message }}</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                  <span class="font-medium">Criado:</span>
                  {{ formatDate(batchDetail.created_at) }}
                </div>
                <div v-if="batchDetail.started_at">
                  <span class="font-medium">Iniciado:</span>
                  {{ formatDate(batchDetail.started_at) }}
                </div>
                <div v-if="batchDetail.completed_at">
                  <span class="font-medium">Finalizado:</span>
                  {{ formatDate(batchDetail.completed_at) }}
                </div>
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
  name: 'Status',
  data() {
    return {
      uploads: [],
      batchDetail: null,
      loading: false,
      loadingDetail: false,
      pollingInterval: null
    }
  },
  async mounted() {
    if (this.$route.params.id) {
      await this.loadBatchDetail();
      this.startPolling();
    } else {
      await this.loadUploads();
    }
  },
  beforeUnmount() {
    this.stopPolling();
  },
  watch: {
    '$route'(to, from) {
      this.stopPolling();
      if (to.params.id) {
        this.loadBatchDetail();
        this.startPolling();
      } else {
        this.loadUploads();
      }
    }
  },
  methods: {
    async loadUploads() {
      this.loading = true;
      try {
        const response = await axios.get('/api/uploads');
        this.uploads = response.data.data || response.data;
      } catch (error) {
      } finally {
        this.loading = false;
      }
    },

    async loadBatchDetail() {
      this.loadingDetail = true;
      try {
        const response = await axios.get(`/api/uploads/${this.$route.params.id}`);
        this.batchDetail = response.data.batch || response.data;
      } catch (error) {
      } finally {
        this.loadingDetail = false;
      }
    },

    async reprocessBatch(id) {
      try {
        await axios.post(`/api/uploads/${id}/reprocess`);
        if (this.$route.params.id) {
          await this.loadBatchDetail();
        } else {
          await this.loadUploads();
        }
      } catch (error) {
      }
    },

    startPolling() {
      this.pollingInterval = setInterval(() => {
        if (this.$route.params.id) {
          this.loadBatchDetail();
        }
      }, 3000); // Poll a cada 3 segundos
    },

    stopPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
        this.pollingInterval = null;
      }
    },

    getStatusClass(status) {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        error: 'bg-red-100 text-red-800'
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },

    getStatusText(status) {
      const texts = {
        pending: 'Pendente',
        processing: 'Processando',
        completed: 'Concluído',
        error: 'Erro'
      };
      return texts[status] || status;
    },

    formatTypeName(type) {
      const names = {
        time_tracking_events: 'Time Tracking',
        store_checkins: 'Store Checkins',
        price_surveys: 'Price Surveys',
        shelf_lives: 'Shelf Lives',
        stock_availabilities: 'Stock Availability',
        media: 'Media'
      };
      return names[type] || type;
    },

    formatDate(dateString) {
      if (!dateString) return '-';
      return new Date(dateString).toLocaleString('pt-BR');
    }
  }
}
</script>