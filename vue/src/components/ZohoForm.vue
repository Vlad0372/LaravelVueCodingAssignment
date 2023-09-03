<template>
    <div @click="showPopup = !showPopup" v-show="showPopup" class="fixed flex justify-center items-center z-10 w-full h-full bg-black bg-opacity-25">
        <div :class="popupColor" class="flex justify-center items-center  rounded p-10">
            <p v-text="popupMsg"></p>
        </div>
    </div>

    <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <img class="mx-auto h-28 w-auto" src="../../zoho.svg" alt="Your Company">
        <h2 class="text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Zoho CRM Web Form</h2>
      </div>
  
      <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
        <form class="space-y-6" method="POST" @submit.prevent="submitForm">
            <div class="rounded-md shadow-sm -space-y-px">
              <h1 class="text-start text-2xl sm:text-lg my-4 sm:my-1 font-light tracking-tight text-gray-900">Deal details</h1>
              <div>
                  <label for="deal-name" class="sr-only">Name</label>
              
                  <input id="deal-name" name="deal-name" type="text" autocomplete="deal-name" 
                  class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                  placeholder="Name" v-model="form.dealName" @keydown="delete form.errors.dealName">         
              </div>

              <div class="p-2 text-red-500 text-sm" v-show="hasProperty('dealName')" v-text="getPropertyValue('dealName')"></div>

              <div>
                  <select @change="delete form.errors.stage" class="rounded-none relative block w-full px-2 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                  v-model="form.stage">
                     <option disabled value="">Select the deal stage </option>
                     <option v-for="stage in this.dealStages" :value="stage.id">{{ stage.stage_name }}</option>
                  </select>
              </div>
              
              <div class="p-2 text-red-500 text-sm" v-show="hasProperty('stage')" v-text="getPropertyValue('stage')"></div>
          </div>

          <div class="rounded-md shadow-sm -space-y-px">
              <h1 class="text-start text-2xl sm:text-lg my-4 sm:my-1 font-light tracking-tight text-gray-900">Account details</h1>
              <div>
                  <label for="account-name" class="sr-only">Name</label>
              
                  <input id="account-name" name="account-name" type="text" autocomplete="account-name" 
                  class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                  placeholder="Name" v-model="form.accountName" @keydown="delete form.errors.accountName">         
              </div>
  
              <div class="p-2 text-red-500 text-sm" v-show="hasProperty('accountName')" v-text="getPropertyValue('accountName')"></div>

              <div>         
                  <label for="website" class="sr-only">Website</label>                    
              
                  <input id="website" name="website" type="text" autocomplete="website"
                  class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                  placeholder="Website" v-model="form.website" @keydown="delete form.errors.website">     
              </div>

              <div class="p-2 text-red-500 text-sm" v-show="hasProperty('website')" v-text="getPropertyValue('website')"></div>

              <div>         
                  <label for="phone" class="sr-only">Phone</label>                    
              
                  <input id="phone" name="phone" type="text" autocomplete="phone" 
                  class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                  placeholder="Phone" v-model="form.phone" @keydown="delete form.errors.phone">     
              </div>

              <div class="p-2 text-red-500 text-sm" v-show="hasProperty('phone')" v-text="getPropertyValue('phone')"></div>
          </div>
  
          <div>
            <button type="submit" class="flex w-full h-9 justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            v-text="btnText" :disabled="btnDisabled"></button>
          </div>
        </form>

      </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            form: {
                dealName: '',
                stage: '',
                accountName: '',
                website: '',
                phone: '',
                errors: [],
            },
            showPopup: false,
            popupMsg: "",
            popupColor: "",
            dealStages: [],
            btnText: "Submit",
            btnDisabled: false,
            btnAnimation: null,
        }
    },

    methods: {
        submitForm() {
            this.startBtnLoadingAnim();

            let data = new FormData();

            data.append('dealName', this.form.dealName);
            data.append('stage', this.form.stage);
            data.append('accountName', this.form.accountName);
            data.append('website', this.form.website);
            data.append('phone', this.form.phone);

            fetch('http://127.0.0.1:8000/api/records', {
                method: 'post',
                body: data,
            })
            .then(response => response.json())
            .then((data) => {
                if(data.hasOwnProperty('request_failed')) {
                    if(data.request_failed == true) {
                        this.showFailPopup(data.response_msg);
                    }else{
                        this.showSuccessPopup('Records have been successffuly created!');

                        this.form.dealName = "";
                        this.form.stage = "";
                        this.form.accountName = "";
                        this.form.website = "";
                        this.form.phone = "";
                    } 
                }else{
                    this.form.errors = data;
                }   
                
                this.stopBtnLoadingAnim();
            }).catch(error => {
                this.stopBtnLoadingAnim();
                this.showFailPopup("Server error. Try again later");
            });              
        },
        hasProperty(prop) {
            return this.form.errors.hasOwnProperty(prop);
        },
        getPropertyValue(prop) {
            if (this.form.errors.hasOwnProperty(prop)) {
                return this.form.errors[prop][0];
            }
            return '';
        },
        showSuccessPopup(msg) {
            this.showPopup = true;
            this.popupColor = 'bg-green-200';
            this.popupMsg = msg;
        },
        showFailPopup(msg) {
            this.showPopup = true;
            this.popupColor = 'bg-red-200';
            this.popupMsg = msg;
        },
        btnLoadingAnim(){
            if(this.btnText == " ■ ■ ■ "){
                this.btnText = " ";
            }else{
                this.btnText += "■ ";
            }     
        },
        startBtnLoadingAnim() {
            this.btnDisabled = true;
            this.btnText = " ";
            this.btnAnimation = setInterval(this.btnLoadingAnim, 250);            
        },
        stopBtnLoadingAnim() {  
            clearInterval(this.btnAnimation);
            this.btnText = "Submit";
            this.btnDisabled = false;
        }
    },
    
    created() {
        fetch("http://127.0.0.1:8000/api/deal-stages")
            .then(response => response.json())
            .then(data => { this.dealStages = data});
    }
}
</script>
  
  