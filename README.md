✅ REQUIREMENTS
✔ UI Features:
✅ Upload button: Present in the form.

✅ Recent uploads list: Displayed in a table.

✅ Upload time and status: Both shown.

✔ Background Job:
✅ File is processed in background using a job (ProcessCsvUpload).

✔ CSV Processing:
✅ Cleaned data and used updateOrCreate for upserting by unique_key.

✅ Conversion of PIECE_PRICE to float.

✅ Transaction + error handling during processing.

✔ Real-time Updates:
✅ Polling (via fetchUploads() every 5 sec).

✅ WebSocket setup using Laravel Echo + Pusher (though not fully functional yet—see below).

✔ Idempotent + Upsert:
✅ updateOrCreate() makes the system idempotent and supports upserting by unique_key.
