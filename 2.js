function putarArray(arr = []) {
    for (let i = 1; i <= 4; i++) {
        let first = arr[0];
        arr.shift();
        arr = [...arr,first];
        console.log(`Putaran ${i} : ${arr}`);
    }
}
putarArray([1,3,4,5,6,7]);