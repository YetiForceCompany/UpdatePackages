<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div>
    <q-tabs
      v-model="historyTab"
      @input="tabChange"
      align="left"
      dense
      shrink
      inline-label
      narrow-indicator
      class="text-teal"
    >
      <q-tab v-for="(room, roomType) of data.roomList" :key="roomType" :name="roomType">
        <icon class="q-icon q-tab__icon" size="20px" :icon="getGroupIcon(roomType)" />
        <span class="q-tab__label">{{ translate(`JS_CHAT_ROOM_${roomType.toUpperCase()}`) }}</span>
      </q-tab>
    </q-tabs>
    <q-tab-panels v-model="historyTab" animated style="min-height: inherit;" class="chat-panels">
      <q-tab-panel v-for="(room, roomType) of data.roomList" :key="roomType" :name="roomType">
        <messages
          @earlierClick="earlierClick"
          :fetchingEarlier="fetchingEarlier"
          :header="messageHeader"
          :roomData="data.history"
          :messageOnClick="showChatRoom"
        />
      </q-tab-panel>
    </q-tab-panels>
  </div>
</template>
<script>
import Messages from './Messages.vue'
import { getGroupIcon } from '../utils/utils.js'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
  name: 'History',
  components: { Messages },
  data() {
    return {
      userId: CONFIG.userId,
      fetchingEarlier: false
    }
  },
  computed: {
    ...mapGetters(['data', 'tab', 'allRooms']),
    historyTab: {
      get() {
        return this.$store.getters['Chat/historyTab']
      },
      set(tab) {
        this.$store.commit('Chat/setHistoryTab', tab)
      }
    }
  },
  methods: {
    ...mapActions(['fetchHistory', 'fetchRoom']),
    ...mapMutations(['setTab']),
    getGroupIcon,
    tabChange(val) {
      this.fetchHistory({ groupHistory: val, showMoreClicked: false })
    },
    earlierClick() {
      this.fetchingEarlier = true
      this.fetchHistory({ groupHistory: this.historyTab, showMoreClicked: true }).then(e => {
        this.fetchingEarlier = false
      })
    },
    showChatRoom(row, e) {
      if (e.target.dataset.showChatRoom) {
        this.fetchRoom({
          id: row.recordid,
          roomType: this.historyTab
        }).then(_ => {
          this.setTab('chat')
        })
      }
    },
    messageHeader(row) {
      const isRoomActive = this.allRooms.some(e => e.recordid === row.recordid)
      let template = `
				<div class="row justify-between${row.userid === this.userId ? ' reverse' : ''}">
					<div>${row.user_name}</div>`
      if (isRoomActive) {
        template += `<a class="text-teal" href="#" data-show-chat-room="true">${row.room_name}</a></div>`
      } else {
        template += `<div class="text-teal" href="#">${row.room_name}</div></div>`
      }
      return template
    }
  },
  mounted() {
    this.fetchHistory({ groupHistory: this.historyTab, showMoreClicked: false }).then(() => {
      this.$emit('onContentLoaded', true)
    })
  }
}
</script>
<style lang="sass">
</style>
