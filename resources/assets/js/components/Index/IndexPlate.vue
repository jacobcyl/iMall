<template>
    <div class="plate-wrapper">
        <a v-link="{name:'aPlate',params:{'hashid':plate.id}}" v-for="plate in plates">
            <img :src="plate.plate_img"/>
        </a>
        <div class="copyright">
            <p>Copyright © 2017 - 2018</p>
            <p>佛山瀚凌科技有限公司</p>
        </div>
    </div>
</template>
<style scoped>
    img{
        display:block;
        width:100%;
        height:auto;
        margin-bottom:10px;
    }
    .copyright{
        color:gray;
        text-align: center
    }
</style>
<script>
    export default{
        data(){
            return{
                plates:''
            }
        },
        created(){
            //this.fetchPlates();
        },
        methods:{
            fetchPlates:function(){
                let vm = this;
                this.$http.get('/api/plates').then(function(response){
                    vm.$set('plates',response.data);
                });
            }
        }
    }

</script>
